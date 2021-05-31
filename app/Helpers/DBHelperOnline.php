<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\DBHelper as DBHelper;

class DBHelperOnline
{

static $CollectPurchase = 'PurchaseOnline';
static $CollectConsume = 'ConsumeOnline';
static $OneClassFee = 300;
//static $ClassKind = [1,4,8];


    public static function hasValidCard($userId)
    {
        $dt = Carbon::now();
        $cards = DB::collection(DBHelperOnline::$CollectPurchase)
            ->where('UserID', $userId)
            //->where('Expired', '>', $dt)
            ->where('Points', '>', 0)
            //->orderBy('CardCreateTime', 'desc')
            ->first();
        return ($cards != null);
    }


    public static function getCardOnline($cardId)
    {
        return DB::collection(DBHelperOnline::$CollectPurchase)->where('CardID', $cardId)->first();
    }
    public static function getCardHistoryOnline($cardId)
    {
        return DB::collection(DBHelperOnline::$CollectPurchase)->where('CardID', $cardId)->get();
    }
    public static function getOnlineCardList()
    {
        return DB::collection(DBHelperOnline::$CollectPurchase)
          ->orderBy('CardCreateTime', 'desc')
          ->get();
    }


    public static function getUserId($cardId)
    {
        $arr = DB::collection(DBHelperOnline::$CollectPurchase)->where('CardID', $cardId);
        if ($arr == null) return "";
        return  $arr->first()['UserID'];
    }


    public static function getUsersNoOnlineCard()
    {
        //get()出來就是array
        //where not avaible card in PurchaseOnline
        $alluser = DBHelper::getUsers();

        $oncardUsers = [];
        if($alluser==null) {
          Log::info('getUsersNoOnlineCard(): henna no user?');
        } else {
          foreach ($alluser as $each) {
            $has = DBHelperOnline::hasValidCard( $each['UserID']);
            if(!$has) {
              array_push($oncardUsers, $each);
            }
          }
        }


        return $oncardUsers;
    }


    public static function buyNewCard($userId, $point, $buydate)
    {
        $cardId = DBHelperOnline::genCardId();

        DBHelperOnline::insertPurchase($cardId, $userId, $point, $buydate);
        return $cardId;
    }

    public static function genCardId()
    {
        $count = DB::collection(DBHelperOnline::$CollectPurchase)
            ->where('CardCreateTime', 'like', '%' . date("Y") . '%')
            ->where('Payment', '>=', 0) //因為可能有退卡，是負的要去掉
            ->where('Payment', '!=', 200) //逾期補繳的要去掉
            ->count() + 1;
        return date("Y") . str_pad($count, 4, '0', STR_PAD_LEFT);

        //return DBHelper::generateRandomString();
    }

    public static function insertPurchase($cardId, $id, $point, $buydate)
    {
        $dt = DBHelper::getMongoDateNow();
        $expired_at = Carbon::now()->endOfYear()->toDateTimeString();
        $dt_expired = DBHelper::strtoMongoDate($expired_at);
        $amount = $point * DBHelperOnline::$OneClassFee;

        $newCard = [
            'CardID' => $cardId,
            'UserID' => $id,
            'Points' => (int)$point,
            "Expired" => $dt_expired,
            "CardCreateTime" => $dt,
        ];
        if ($amount) {
            $newCard['Payment'] = $amount;
            $newCard['PaymentTime'] = DBHelper::strtoMongoDate($buydate);
        } else {
            $newCard['Payment'] = null;
            $newCard['PaymentTime'] = null;
        }

        Log::info('insertPurchase =' . json_encode($newCard));

        DB::collection(DBHelperOnline::$CollectPurchase)
            ->insert($newCard);
    }

    public static function getOnlineHistory($userId)
    {
        return DB::collection(DBHelperOnline::$CollectPurchase)
            ->where('UserID', $userId)
            ->where('Payment', '>', 0) //因為可能有退卡，是負的要去掉
            //->where('Payment', '!=', 200) //逾期補繳的要去掉
            ->get();
    }

    public static function isSoloCard($cardId)
    {
        $card = DB::collection(DBHelperOnline::$CollectPurchase)->where('CardID', $cardId)->first();
        return ($card['Payment'] == DBHelperOnline::$OneClassFee);
    }

    public static function getConsumeList($cardId)
    {
        //get()出來就是array
        return DB::collection(DBHelperOnline::$CollectConsume)
            ->select('PointConsumeTime')
            ->where('CardID', $cardId)
            ->orderBy('PointConsumeTime')->get();
    }

    public static function getLiveCards()
    {
        //get()出來就是array
        return DB::collection(DBHelperOnline::$CollectPurchase)
            ->where('Points', '>', 0)
            ->get();
    }

    public static function getAllCards()
    {
        //get()出來就是array
        return DB::collection(DBHelperOnline::$CollectPurchase)
            ->where('Payment', '>', 0)
            ->get();
    }

    public static function isConsumeByDate($cardId, $dt)
    {
        $datas = DB::collection(DBHelperOnline::$CollectConsume)
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

    public static function registeByPoint($cardId, $point)
    {
        $newdata = array('$set' => array(
            'Points' => $point - 1,
        ));
        DB::collection(DBHelperOnline::$CollectPurchase)
            ->where('CardID', $cardId)
            ->where('Payment', '>', 0) //因為可能有退卡，是負的要去掉
            //->where('Payment', '!=', 200) //逾期補繳的要去掉
            ->update($newdata);
    }

    public static function insertConsume($cardId, $point, $dt)
    {
        $newCard = [
            'CardID' => $cardId,
            'UserID' => DBHelper::getUserId($cardId),
            "Cost" => DBHelperOnline::$OneClassFee,
            "PointConsumeTime" => $dt,
        ];
        DB::collection(DBHelperOnline::$CollectConsume)
            ->insert($newCard);
    }

    public static function getConsumeByCardOnline($cardId)
    {
        //get()出來就是array
        return DB::collection(DBHelperOnline::$CollectConsume)
            ->where('CardID', $cardId)
            ->orderBy('PointConsumeTime')->get();
    }

    public static function getUserId($cardId)
    {
        $arr = DB::collection(DBHelperOnline::$CollectPurchase)->where('CardID', $cardId);
        if ($arr == null) return "";
        return  $arr->first()['UserID'];
    }

    //退款
    public static function refund($cardId, $amount)
    {
        //清空點數
        DBHelperOnline::clearPoints($cardId);

        //新增一筆金額為負的，同卡號
        $dt = DBHelper::getMongoDateNow();

        $newCard = [
            'CardID' => $cardId,
            'UserID' => DBHelperOnline::getUserId($cardId),
            'Points' => 0,
            "Expired" => $dt,
            "CardCreateTime" => $dt,
            "Payment" => -$amount,
            "PaymentTime" => $dt,
        ];

        DB::collection(DBHelperOnline::$CollectPurchase)
            ->insert($newCard);
    }

    //該卡號有負數的紀錄的話，表示已退款過
    public static function isRefundable($cardId)
    {
        $datas = DB::collection(DBHelperOnline::$CollectPurchase)
            ->where('CardID', $cardId)
            ->where('Payment', '<', 0) //退卡的
            ->get();
        //Log::info('DBHelper::isRefundable size(Payment<0)=' . sizeof($datas));
        if (sizeof($datas) > 0)
            return true;
        else
            return false;
    }

    public static function clearPoints($cardId)
    {
        $newdata = array('$set' => array(
            'Points' => 0,
        ));
        DB::collection(DBHelperOnline::$CollectPurchase)
            ->where('CardID', $cardId)
            ->where('Points', ">", 0)
            ->update($newdata);
    }

    public static function thisYearCount($userId)
    {
        $date = date("Y-01-01");
        return sizeof(DB::collection(DBHelperOnline::$CollectConsume)
            ->where('UserID', $userId)
            ->where('PointConsumeTime', '>=', DBHelper::parse($date))
            ->get());
    }

    public static function thisMonthCount($userId)
    {
        $date = date("Y-m-01");
        return sizeof(DB::collection(DBHelperOnline::$CollectConsume)
            ->where('UserID', $userId)
            ->where('PointConsumeTime', '>=', DBHelper::parse($date))
            ->get());
    }

}
