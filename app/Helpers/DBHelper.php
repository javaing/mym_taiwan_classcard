<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DBHelper
{
    public static function getMongoDateNow()
    {
        $created_at = Carbon::now()->toDateTimeString();
        return new \MongoDB\BSON\UTCDateTime(strtotime($created_at) * 1000);
    }

    public static function toDateString($dbdate)
    {
        if (!$dbdate) return '';
        return $dbdate->toDateTime()->format('Y-m-d');
    }

    public static function toDateStringShort($dbdate)
    {
        if (!$dbdate) return '';
        return $dbdate->toDateTime()->format('y-m-d');
    }

    public static function parse($str)
    {
        //Log::info('parse=' . Carbon::parse($str));
        return Carbon::parse($str);
    }


    //[Purchase]------------------------------------------------------------------
    public static function getValidCard($userId)
    {
        $dt = Carbon::now();
        return DB::collection('Purchase')
            ->where('UserID', $userId)
            ->where('Expired', '>', $dt)
            ->where('Points', '>', 0)
            ->first();
    }

    public static function getValidCardNoMatter($userId)
    {
        $dt = Carbon::now();
        return DB::collection('Purchase')
            ->where('UserID', $userId)
            ->where('Expired', '>', $dt)
            //->where('Points', '>', 0)
            ->orderBy('Expired', 'asce')
            ->first();
    }

    public static function getCard($cardId)
    {
        return DB::collection('Purchase')->where('CardID', $cardId)->first();
    }
    public static function getCardHistory($cardId)
    {
        return DB::collection('Purchase')->where('CardID', $cardId)->get();
    }

    public static function getUserId($cardId)
    {
        $arr = DB::collection('Purchase')->where('CardID', $cardId);
        if (!$arr) return "";
        return  $arr->first()['UserID'];
    }

    public static function getCardId()
    {
        $count = DB::collection('Purchase')
            ->where('CardCreateTime', 'like', '%' . date("Y") . '%')
            ->where('Payment', '>', 0) //因為可能有退卡，是負的要去掉
            ->count() + 1;
        return date("Y") . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public static function registeclassByPoint($cardId, $point)
    {
        $newdata = array('$set' => array(
            'Points' => $point - 1,
        ));
        DB::collection('Purchase')
            ->where('CardID', $cardId)
            ->update($newdata);
    }

    public static function isExpiredCard($cardId)
    {
        $card = DB::collection('Purchase')->where('CardID', $cardId)->first();
        return ($card['Payment'] == 200) ? true : false;
    }

    public static function getBalanceIn($from, $to)
    {
        //get()出來就是array
        return DB::collection('Purchase')
            ->where('PaymentTime', '>=', DBHelper::parse($from))
            ->where('PaymentTime', '<', DBHelper::parse($to))
            ->get();
    }


    //[UserInfo]--------------------------------------------------------
    public static function getUser($userId)
    {
        return  DB::collection('UserInfo')->where('UserID', $userId)->first();
    }

    public static function getUserName($userId)
    {
        return  DB::collection('UserInfo')->where('UserID', $userId)->first()['NickName'];
    }

    public static function getUsers()
    {
        return  DB::collection('UserInfo')->get();
    }

    public static function updateUser($userId, $datas)
    {
        $newdata = array('$set' => $datas);
        DB::collection('UserInfo')
            ->where('UserID', $userId)
            ->update($newdata);
    }

    //[Consume]----------------------------------------------------------
    public static function getConsume($cardId)
    {
        //get()出來就是array
        return DB::collection('Consume')
            ->select('PointConsumeTime')
            ->where('CardID', $cardId)
            ->orderBy('PointConsumeTime')->get();
    }

    public static function getConsumeByCard($cardId)
    {
        //get()出來就是array
        return DB::collection('Consume')
            ->where('CardID', $cardId)
            ->orderBy('PointConsumeTime')->get();
    }

    public static function getBalanceOut($from, $to)
    {
        //get()出來就是array
        return DB::collection('Consume')
            ->where('PointConsumeTime', '>=', DBHelper::parse($from))
            ->where('PointConsumeTime', '<', DBHelper::parse($to))
            ->get();
    }



    public static function insertNewUser($user_profile)
    {

        DB::collection('UserInfo')
            ->insert([
                'UserID' => $user_profile['userId'],
                "NickName" => $user_profile['displayName'],
                "UserName" => "",
                "Email" => $user_profile['email'],
                "PictureUrl" => $user_profile['pictureUrl'],
                "Mobile" => "",
                "Address" => "",
                "Referrer" => "",
            ]);
    }

    public static function buyNewCard($userId)
    {
        $amount = 1800;
        $point = 4;
        DBHelper::insertPurchase($userId, $amount, $point);
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

        //Log::info('insertPurchase =' . json_encode($newCard));

        DB::collection('Purchase')
            ->insert($newCard);
    }

    public static function refund($cardId, $amount)
    {
        //清空點數
        $newdata = array('$set' => array(
            'Points' => 0,
        ));
        DB::collection('Purchase')
            ->where('CardID', $cardId)
            ->update($newdata);


        //新增一筆金額為負的
        $dt = DBHelper::getMongoDateNow();

        $newCard = [
            'CardID' => $cardId,
            'UserID' => DBHelper::getUserId($cardId),
            'Points' => 0,
            "Expired" => $dt,
            "CardCreateTime" => $dt,
            "Payment" => -$amount,
            "PaymentTime" => $dt,
        ];

        DB::collection('Purchase')
            ->insert($newCard);
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

    public static function isDeposited($cardId, $cost)
    {
        $today = date("Y-m-d");
        $datas = DB::collection('Consume')
            ->where('CardID', $cardId)
            ->where('UserID', DBHelper::getUserId($cardId))
            ->where('Cost', $cost)
            ->where('PointConsumeTime', '>=', DBHelper::parse($today))
            ->get();
        Log::info('DBHelper::isDeposited =' . sizeof($datas) . ",date=" . $today);
        if (sizeof($datas) > 0)
            return true;
        else
            return false;
    }
}
