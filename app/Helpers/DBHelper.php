<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DBHelper
{
    public static function getValidCard($userId)
    {
        //$id = $request->userId;
        $dt = Carbon::now();
        return DB::collection('Purchase')
            ->where('UserID', $userId)
            ->where('Expired', '>', $dt)
            ->where('Points', '>', 0)
            ->first();
    }

    public static function getValidCardNoMatter($userId)
    {
        //$id = $request->userId;
        $dt = Carbon::now();
        return DB::collection('Purchase')
            ->where('UserID', $userId)
            ->where('Expired', '>', $dt)
            //->where('Points', '>', 0)
            ->orderBy('Expired', 'asce')
            ->first();
    }

    public static function getUser($userId)
    {
        //$id = $request->userId;
        return  DB::collection('UserInfo')->where('UserID', $userId)->first();
    }

    public static function getUserName($userId)
    {
        return  DB::collection('UserInfo')->where('UserID', $userId)->first()['NickName'];
    }

    public static function getCard($cardId)
    {
        return DB::collection('Purchase')->where('CardID', $cardId)->first();
    }

    public static function getUserId($cardId)
    {
        $arr = DB::collection('Purchase')->where('CardID', $cardId);
        if (!$arr) return "";
        return  $arr->first()['UserID'];
    }

    public static function toDateString($dbdate)
    {
        if (!$dbdate) return '';
        return $dbdate->toDateTime()->format('Y-m-d');
    }

    public static function getConsume($cardId)
    {
        //get()出來就是array
        return DB::collection('Consume')
            ->select('PointConsumeTime')
            ->where('CardID', $cardId)
            ->orderBy('PointConsumeTime')->get();
    }

    public static function insertNewUser($user_profile)
    {
        DB::collection('UserInfo')
            ->insert([
                'UserID' => $user_profile['userId'],
                "NickName" => $user_profile['displayName'],
                "Email" => $user_profile['email'],
                "PictureUrl" => $user_profile['pictureUrl'],
            ]);
        Log::info('pag()=insert UserInfo');
    }

    public static function buyNewCard($userId)
    {
        $amount = 1800;
        $point = 4;
        DBHelper::insertPurchase($userId, $amount, $point);
    }

    public static function getMongoDateNow()
    {
        $created_at = Carbon::now()->toDateTimeString();
        return new \MongoDB\BSON\UTCDateTime(strtotime($created_at) * 1000);
    }

    public static function getCardId()
    {
        $count = DB::collection('Purchase')->where('CardCreateTime', 'like', '%' . date("Y") . '%')->count() + 1;
        return date("Y") . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public static function insertPurchase($id, $amount, $point)
    {
        $dt = DBHelper::getMongoDateNow();
        $expired_at = Carbon::now()->add(60, 'day')->toDateTimeString();
        $dt_expired = new \MongoDB\BSON\UTCDateTime(strtotime($expired_at) * 1000);

        $newCard = [
            'CardID' => DBHelper::getCardId(),
            'UserID' => $id,
            'Points' => $point,
            "Expired" => $dt_expired,
            "CardCreateTime" => $dt,
        ];
        if ($amount) {
            $newCard['Payment'] = $amount;
            $newCard['PaymentTime'] = $dt;
        } else {
            $newCard['Payment'] = null;
            $newCard['PaymentTime'] = null;
        }

        Log::info('insertPurchase =' . json_encode($newCard));

        DB::collection('Purchase')
            ->insert($newCard);
    }

    public static function registeclassByPoint($cardId, $point)
    {
        $newdata = array('$set' => array(
            'Points' => $point - 1,
        ));
        DB::collection('Purchase')
            ->where('CardID', $cardId)
            ->update($newdata, ['upsert' => true]);
    }

    public static function isExpiredCard($cardId)
    {
        $card = DB::collection('Purchase')->where('CardID', $cardId)->first();
        return ($card['Payment'] == 200) ? true : false;
    }


    public static function insertConsume($cardId, $point)
    {
        $cost = 500;
        if ($point == 1 && !DBHelper::isExpiredCard($cardId)) {
            $cost = 300;
        }

        $newCard = [
            'CardID' => $cardId,
            'UserID' => DBHelper::getUserId($cardId),
            "Cost" => $cost,
            "PointConsumeTime" => DBHelper::getMongoDateNow(),
        ];
        DB::collection('Consume')
            ->insert($newCard);
    }
}
