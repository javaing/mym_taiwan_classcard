<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DBHelperTaichung
{
    public static $collection = 'ActivityCheckin';

    public static function getTodayRecord($userId, $activityType, $businessDate)
    {
        return DB::collection(self::$collection)
            ->where('Branch', config('taichung.branch'))
            ->where('UserID', $userId)
            ->where('ActivityType', $activityType)
            ->where('BusinessDate', $businessDate)
            ->first();
    }

    public static function getTodayActivityTypes($userId, $businessDate)
    {
        return DB::collection(self::$collection)
            ->where('Branch', config('taichung.branch'))
            ->where('UserID', $userId)
            ->where('BusinessDate', $businessDate)
            ->pluck('ActivityType')
            ->all();
    }

    public static function getTodayCheckedActivityTypes($userId, $businessDate)
    {
        return DB::collection(self::$collection)
            ->where('Branch', config('taichung.branch'))
            ->where('UserID', $userId)
            ->where('BusinessDate', $businessDate)
            ->whereNotNull('CheckinTime')
            ->pluck('ActivityType')
            ->all();
    }

    public static function isCheckedIn($record)
    {
        return !empty($record) && !empty($record['CheckinTime']);
    }

    public static function hasPrepaid($userId, $activityType, $businessDate)
    {
        return DB::collection(self::$collection)
            ->where('Branch', config('taichung.branch'))
            ->where('UserID', $userId)
            ->where('ActivityType', $activityType)
            ->where('BusinessDate', $businessDate)
            ->exists();
    }

    public static function hasCheckedIn($userId, $activityType, $businessDate)
    {
        return DB::collection(self::$collection)
            ->where('Branch', config('taichung.branch'))
            ->where('UserID', $userId)
            ->where('ActivityType', $activityType)
            ->where('BusinessDate', $businessDate)
            ->whereNotNull('CheckinTime')
            ->exists();
    }

    /**
     * 新增現金預收。尚未蓋點時不寫 CheckinTime。
     * 回傳 false 表示同日同活動已有紀錄（唯一索引）。
     */
    public static function insertCashPrepaid($userId, $activityType, $activity, $businessDate)
    {
        $now = DBHelper::getMongoDateNow();

        try {
            DB::collection(self::$collection)->insert([
                'UserID' => $userId,
                'Branch' => config('taichung.branch'),
                'ActivityType' => $activityType,
                'ActivityName' => $activity['label'],
                'Amount' => (int) $activity['amount'],
                'PaymentMethod' => 'cash',
                'BusinessDate' => $businessDate,
                'PaidAt' => $now,
                'Source' => 'static_qr',
                'CreatedAt' => $now,
            ]);
        } catch (\Exception $exception) {
            if ((int) $exception->getCode() === 11000) {
                return false;
            }

            throw $exception;
        }

        return true;
    }

    /**
     * 對已預收、尚未蓋點的紀錄寫入 CheckinTime。
     * 回傳 false 表示沒有可蓋點的預收（未付費或已蓋過）。
     */
    public static function tryCheckin($userId, $activityType, $businessDate)
    {
        $updated = DB::collection(self::$collection)
            ->where('Branch', config('taichung.branch'))
            ->where('UserID', $userId)
            ->where('ActivityType', $activityType)
            ->where('BusinessDate', $businessDate)
            ->whereNull('CheckinTime')
            ->update(['$set' => ['CheckinTime' => DBHelper::getMongoDateNow()]]);

        return $updated > 0;
    }

    /**
     * 後台補登：尚未預收則一次寫入預收＋報到；已預收未報到則補報到。
     * 回傳 created / checked_in / duplicate / invalid。
     */
    public static function staffPrepaidAndCheckin($userId, $activityType, $businessDate)
    {
        $activities = config('taichung.activities');
        if (!is_string($activityType) || !isset($activities[$activityType])) {
            return 'invalid';
        }

        $activity = $activities[$activityType];
        $at = new \MongoDB\BSON\UTCDateTime(
            Carbon::parse($businessDate, 'Asia/Taipei')->setTime(12, 0)->getTimestamp() * 1000
        );
        $record = self::getTodayRecord($userId, $activityType, $businessDate);

        if ($record && self::isCheckedIn($record)) {
            return 'duplicate';
        }

        if ($record) {
            $updated = DB::collection(self::$collection)
                ->where('Branch', config('taichung.branch'))
                ->where('UserID', $userId)
                ->where('ActivityType', $activityType)
                ->where('BusinessDate', $businessDate)
                ->whereNull('CheckinTime')
                ->update(['$set' => ['CheckinTime' => $at]]);

            return $updated > 0 ? 'checked_in' : 'duplicate';
        }

        try {
            DB::collection(self::$collection)->insert([
                'UserID' => $userId,
                'Branch' => config('taichung.branch'),
                'ActivityType' => $activityType,
                'ActivityName' => $activity['label'],
                'Amount' => (int) $activity['amount'],
                'PaymentMethod' => 'cash',
                'BusinessDate' => $businessDate,
                'PaidAt' => $at,
                'CheckinTime' => $at,
                'Source' => 'staff_byhand',
                'CreatedAt' => DBHelper::getMongoDateNow(),
            ]);
        } catch (\Exception $exception) {
            if ((int) $exception->getCode() === 11000) {
                return 'duplicate';
            }
            throw $exception;
        }

        return 'created';
    }

    public static function getPrepaidInRange($from, $to, $userId = null)
    {
        $filterUser = func_num_args() >= 3;

        $paid = DB::collection(self::$collection)
            ->where('PaidAt', '>=', $from)
            ->where('PaidAt', '<', $to);
        if ($filterUser) {
            $paid->where('UserID', $userId);
        }

        $legacy = DB::collection(self::$collection)
            ->whereNull('PaidAt')
            ->where('CheckinTime', '>=', $from)
            ->where('CheckinTime', '<', $to);
        if ($filterUser) {
            $legacy->where('UserID', $userId);
        }

        return $paid->get()->merge($legacy->get());
    }

    public static function getBalanceIn($from, $to)
    {
        return self::getPrepaidInRange(DBHelper::parse($from), DBHelper::parseEndExclusive($to))
            ->map(function ($each) {
                $each['Payment'] = $each['Amount'];
                $each['PaymentTime'] = $each['PaidAt'] ?? $each['CheckinTime'] ?? null;
                return $each;
            });
    }
}
