<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DBHelper
{
    public static function shout(string $string)
    {
        return strtoupper($string);
    }

    public static function getValidCard($id)
    {
        //$id = $request->userId;
        $dt = Carbon::now();
        return DB::collection('Purchase')
            ->where('UserID', $id)
            ->where('Expired', '>', $dt)
            ->where('Points', '>', 0)
            ->first();
    }

    public static function getUser($id)
    {
        //$id = $request->userId;
        return  DB::collection('UserInfo')->where('UserID', $id)->first();
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

    public static function buyClassCard($userId, $amount)
    {
        $status = 200;
        $content = "success";

        if (!DBHelper::getUser($userId)) {
            $content = "無此使用者，請先登入";
            return response($content, $status);
        }

        //是否有舊卡
        $card = DBHelper::getValidCard($userId);
        if ($card) {
            $card['message'] = "尚有點數可用";
            $content = $card;
            return response($content, $status);
        }

        $point = 4;
        DBHelper::insertPurchase($userId, $amount, $point);

        return response($content, $status);
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
}
