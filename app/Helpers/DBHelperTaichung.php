<?php

namespace App\Helpers;

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
        return self::getPrepaidInRange(DBHelper::parse($from), DBHelper::parse($to))
            ->map(function ($each) {
                $each['Payment'] = $each['Amount'];
                $each['PaymentTime'] = $each['PaidAt'] ?? $each['CheckinTime'] ?? null;
                return $each;
            });
    }
}
