<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Exception\BulkWriteException;

class DBHelperTaichung
{
    public static $collection = 'ActivityCheckin';

    public static function getTodayActivityTypes($userId, $businessDate)
    {
        return DB::collection(self::$collection)
            ->where('Branch', config('taichung.branch'))
            ->where('UserID', $userId)
            ->where('BusinessDate', $businessDate)
            ->pluck('ActivityType')
            ->all();
    }

    public static function hasCheckedIn($userId, $activityType, $businessDate)
    {
        return DB::collection(self::$collection)
            ->where('Branch', config('taichung.branch'))
            ->where('UserID', $userId)
            ->where('ActivityType', $activityType)
            ->where('BusinessDate', $businessDate)
            ->exists();
    }

    /**
     * 新增現金報到紀錄。
     *
     * 唯一索引會保證同據點、會員、活動、日期只能有一筆；
     * 回傳 false 表示同日重複，其他資料庫錯誤仍往外拋出。
     */
    public static function insertCashCheckin($userId, $activityType, $activity, $businessDate)
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
                'CheckinTime' => $now,
                'Source' => 'static_qr',
                'CreatedAt' => $now,
            ]);
        } catch (BulkWriteException $exception) {
            if ((int) $exception->getCode() === 11000) {
                return false;
            }

            throw $exception;
        }

        return true;
    }
}
