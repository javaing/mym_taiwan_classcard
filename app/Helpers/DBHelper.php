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
        return $dbdate->toDateTime()->format('Y/m/d');
    }

    public static function todaySlash()
    {
        return DBHelper::toDateString(Carbon::now());
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
        $cards = DB::collection('Purchase')
            ->where('UserID', $userId)
            ->where('Expired', '>', $dt)
            ->where('Points', '>', 0)
            ->orderBy('CardCreateTime', 'desc')
            ->first();
        if ($cards != null)
            return $cards;

        return DB::collection('Purchase')
            ->where('UserID', $userId)
            ->where('Expired', '=', null)
            ->where('Points', '>', 0)
            ->first();
    }

    public static function getValidCardNoMatter($userId)
    {
        $dt = Carbon::now();
        $cards = DB::collection('Purchase')
            ->where('UserID', $userId)
            //->where('Expired', '>', $dt)
            //->where('Points', '>', 0)
            ->orderBy('CardCreateTime', 'desc')
            ->first();
        if ($cards != null)
            return $cards;

        return DB::collection('Purchase')
            ->where('UserID', $userId)
            ->where('Expired', '=', null)
            //->where('Points', '>', 0)
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
    public static function getUserHistory($userId)
    {
        return DB::collection('Purchase')
            ->where('UserID', $userId)
            ->where('Payment', '>', 0) //因為可能有退卡，是負的要去掉
            ->get();
    }

    public static function getUserId($cardId)
    {
        $arr = DB::collection('Purchase')->where('CardID', $cardId);
        if (!$arr) return "";
        return  $arr->first()['UserID'];
    }

    static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getCardId()
    {
        $count = DB::collection('Purchase')
            ->where('CardCreateTime', 'like', '%' . date("Y") . '%')
            ->where('Payment', '>', 0) //因為可能有退卡，是負的要去掉
            ->count() + 1;
        return date("Y") . str_pad($count, 4, '0', STR_PAD_LEFT);

        //return DBHelper::generateRandomString();
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

    public static function isSingleClassCard($cardId)
    {
        $card = DB::collection('Purchase')->where('CardID', $cardId)->first();
        return ($card['Payment'] == 500);
    }

    public static function isExpired($card)
    {
        if ($card['Expired'] == null) return false;
        $dt = DBHelper::getMongoDateNow();
        return ($dt > $card['Expired'] && $card['Points'] > 0);
    }

    public static function isExtendCard($cardId)
    {
        $card = DB::collection('Purchase')->where('CardID', $cardId)->first();
        return ($card['Payment'] == 200);
    }

    public static function getBalanceIn($from, $to)
    {
        //get()出來就是array
        return DB::collection('Purchase')
            ->where('PaymentTime', '>=', DBHelper::parse($from))
            ->where('PaymentTime', '<', DBHelper::parse($to))
            //->where('Payment', '>', 0)
            ->get();
    }

    public static function getBalanceByUserIn($userId)
    {
        return DB::collection('Purchase')
            ->where('UserID', $userId)
            ->get();
    }

    public static function getLiveCards()
    {
        //get()出來就是array
        return DB::collection('Purchase')
            ->where('Points', '>', 0)
            ->get();
    }


    //[UserInfo]--------------------------------------------------------
    public static function getUser($userId)
    {
        return  DB::collection('UserInfo')->where('UserID', $userId)->first();
    }

    public static function getUserName($userId)
    {
        $user = DB::collection('UserInfo')->where('UserID', $userId)->first();
        if ($user['UserName'] != '')
            return $user['UserName'];
        else
            return $user['NickName'];
    }

    public static function getNickName($userId)
    {
        return DB::collection('UserInfo')->where('UserID', $userId)->first()['NickName'];
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

    public static function getBalanceByUserOut($userId)
    {
        return DB::collection('Consume')
            ->where('UserID', $userId)
            ->get();
    }


    public static function thisYearCount($userId)
    {
        $date = date("Y-01-01");
        return sizeof(DB::collection('Consume')
            ->where('UserID', $userId)
            ->where('PointConsumeTime', '>=', DBHelper::parse($date))
            ->get());
    }

    public static function thisMonthCount($userId)
    {
        $date = date("Y-m-01");
        return sizeof(DB::collection('Consume')
            ->where('UserID', $userId)
            ->where('PointConsumeTime', '>=', DBHelper::parse($date))
            ->get());
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

    public static function buyNewCard($userId, $point)
    {
        if ($point == 1)
            DBHelper::insertPurchaseNoExpired($userId, 500, $point);
        else
            DBHelper::insertPurchase($userId, 1800, $point);
    }

    public static function clearPoints($cardId)
    {
        $newdata = array('$set' => array(
            'Points' => 0,
        ));
        DB::collection('Purchase')
            ->where('CardID', $cardId)
            ->update($newdata);
    }

    //逾期補差額:舊卡點數轉移至新卡
    public static function extendCard($userId, $cardId)
    {
        //$cardId = base64_decode($cardId);//不是給網頁call，不用de_base6
        $card = DBHelper::getCard($cardId);
        $point = $card['Points'];

        //清空點數
        DBHelper::clearPoints($cardId);

        $amount = 200;
        DBHelper::insertPurchaseNoExpired($userId, $amount, $point);
    }

    public static function insertPurchase($id, $amount, $point)
    {
        $dt = DBHelper::getMongoDateNow();
        $expired_at = Carbon::now()->add(60, 'day')->toDateTimeString();
        $dt_expired = new \MongoDB\BSON\UTCDateTime(strtotime($expired_at) * 1000);

        $newCard = [
            'CardID' => DBHelper::getCardId(),
            'UserID' => $id,
            'Points' => (int)$point,
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

    public static function insertPurchaseNoExpired($id, $amount, $point)
    {
        $dt = DBHelper::getMongoDateNow();
        $newCard = [
            'CardID' => DBHelper::getCardId(),
            'UserID' => $id,
            'Points' => (int)$point,
            "Expired" => null,
            "CardCreateTime" => $dt,
        ];
        if ($amount) {
            $newCard['Payment'] = $amount;
            $newCard['PaymentTime'] = $dt;
        } else {
            $newCard['Payment'] = null;
            $newCard['PaymentTime'] = null;
        }

        Log::info('insertPurchaseNoExpired =' . json_encode($newCard));

        DB::collection('Purchase')
            ->insert($newCard);
    }

    public static function refund($cardId, $amount)
    {
        //清空點數
        DBHelper::clearPoints($cardId);

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

    public static function insertConsumeToday($cardId, $point)
    {
        DBHelper::insertConsume($cardId, $point, DBHelper::getMongoDateNow());
    }

    public static function insertConsume($cardId, $point, $dt)
    {
        $cost = 500;
        if ($point == 1) {
            if (DBHelper::isSingleClassCard($cardId)) {
                //$cost = 500;
            } else if (!DBHelper::isExtendCard($cardId)) {
                $cost = 300;
            }
        }

        $newCard = [
            'CardID' => $cardId,
            'UserID' => DBHelper::getUserId($cardId),
            "Cost" => $cost,
            "PointConsumeTime" => $dt,
        ];
        DB::collection('Consume')
            ->insert($newCard);
    }

    public static function today()
    {
        $today = date("Y-m-d");
        return DBHelper::parse($today);
    }



    public static function isConsume($cardId)
    {
        return DBHelper::isConsumeByDate($cardId, DBHelper::today());
    }

    public static function isConsumeByDate($cardId, $dt)
    {
        $datas = DB::collection('Consume')
            ->where('CardID', $cardId)
            ->where('UserID', DBHelper::getUserId($cardId))
            ->where('PointConsumeTime', '>=', $dt)
            ->get();
        Log::info('DBHelper::isConsume =' . sizeof($datas) . ",date=" . DBHelper::today());
        if (sizeof($datas) > 0)
            return true;
        else
            return false;
    }

    public static function isDeposited($cardId, $cost)
    {
        $datas = DB::collection('Consume')
            ->where('CardID', $cardId)
            ->where('UserID', DBHelper::getUserId($cardId))
            ->where('Cost', $cost)
            ->where('PointConsumeTime', '>=', DBHelper::today())
            ->get();
        Log::info('DBHelper::isDeposited =' . sizeof($datas) . ",date=" . DBHelper::today());
        if (sizeof($datas) > 0)
            return true;
        else
            return false;
    }
}
