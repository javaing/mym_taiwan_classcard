<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\DBHelperOnline as DBHelperOnline;

class DBHelper
{
    public static function strtoMongoDate($str) {
        return new \MongoDB\BSON\UTCDateTime(strtotime($str) * 1000);
    }

    public static function getMongoDateNow()
    {
        $created_at = Carbon::now()->toDateTimeString();
        return DBHelper::strtoMongoDate($created_at);
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

    public static function toMMDD($dbdate)
    {
        if (!$dbdate) return '';
        return $dbdate->toDateTime()->format('m-d');
    }

    public static function toMonth($dbdate)
    {
        if (!$dbdate) return '';
        return $dbdate->toDateTime()->format('m');
    }

    public static function toDateStringShort($dbdate)
    {
        if (!$dbdate) return '';
        return $dbdate->toDateTime()->format('y-m-d');
    }

    public static function toDateStringJS($dbdate)
    {
        if (!$dbdate) return '';
        return $dbdate->toDateTime()->format('Y-m-d');
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
        if ($cards != null) {
            return $cards;
        }

        return DB::collection('Purchase')
            ->where('UserID', $userId)
            ->where('Expired', '=', null)
            ->where('Points', '>', 0)
            ->orderBy('CardCreateTime', 'desc')
            ->first();
    }

    public static function getValidCardNoMatter($userId)
    {
        $dt = Carbon::now();
        $cards = DB::collection('Purchase')
            ->where('UserID', $userId)
            //->where('Expired', '>', $dt)
            ->where('Payment', '>', 0)
            ->orderBy('CardCreateTime', 'desc')
            ->first();
        if ($cards != null)
            return $cards;

        return DB::collection('Purchase')
            ->where('UserID', $userId)
            ->where('Expired', '=', null)
            ->where('Payment', '>', 0)
            ->orderBy('CardCreateTime', 'desc')
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
            ->where('Payment', '!=', 200) //逾期補繳的要去掉
            ->get();
    }

    public static function getUserId($cardId)
    {
        $arr = DB::collection('Purchase')->where('CardID', $cardId);
        if ($arr == null) return "";
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
            ->where('Payment', '>=', 0) //因為可能有退卡，是負的要去掉
            ->where('Payment', '!=', 200) //逾期補繳的要去掉
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
            ->where('Payment', '>', 0) //因為可能有退卡，是負的要去掉
            ->where('Payment', '!=', 200) //逾期補繳的要去掉
            ->update($newdata);
    }

    public static function isSingleClassCard($cardId)
    {
        $card = DB::collection('Purchase')->where('CardID', $cardId)->first();
        return ($card['Payment'] == 500);
    }

    public static function isExpired($card)
    {
        if ($card == null || $card['Expired'] == null) return false;
        $dt = DBHelper::getMongoDateNow();
        return ($dt > $card['Expired'] && $card['Points'] > 0);
    }

    public static function isExtendCard($cardId)
    {
        $card = DB::collection('Purchase')->where('CardID', $cardId)->first();
        return ($card['Expired'] == null);
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

    // static function containEmails($str, array $arr)
    // {
    //     foreach ($arr as $a) {
    //         if ($a['Email'] == $str) return true;
    //     }
    //     return false;
    // }

    // static function distinctEmails($dataArray)
    // {
    //     $arr = []; //挑email，需distinct
    //     foreach ($dataArray as $each) {
    //         $email = DBHelper::getUser($each['UserID'])['Email'];
    //         if (!DBHelper::containEmails($email, $arr)) {
    //             $distinct = ['Email' => $email, 'UserID' => $each['UserID']];
    //             array_push($arr, $distinct);
    //         }
    //     }
    //     return $arr;
    // }

    public static function isInRange($date, $from, $to)
    {
        if ($date >= $from && $date <= $to) {
            return true;
        }
        return false;
    }



    static $tableTypeAsana = '體位法';
    static $tableTypeStudyGroup = '讀書會';
    //static $tableTypeAsanaExtent = '體位法補兩百';
    static $tableColumnPayDay = '匯(付)款日期';
    static $tableColumnCnEnName = '中文全名／英文名';
    static $tableColumnAmount = '匯(付)款總金額';

    //需確認 activity(StudyGroup)欄位名稱
    //activity與Purchase如何關聯，Name
    //各挑各的資料，再轉成一致格式
    public static function getBalanceInJoin($Name, $from, $to)
    {
        $totlaRecord = DBHelper::genReportData($Name, $from, $to);
        $totlaRecord = DBHelper::sortByName($totlaRecord);

        return $totlaRecord;
    }

    public static function getBalanceInJoinByType($Name, $from, $to)
    {
        $totlaRecord = DBHelper::genReportData($Name, $from, $to);
        $totlaRecord = DBHelper::sortByType($totlaRecord);

        return $totlaRecord;
    }

    public static function dateShiftFrom($from) {
      return DBHelper::parse($from)->addMonths(-1)->addDays(25)->format('Y-m-d');
    }

    public static function dateShiftTo($to) {
      return DBHelper::parse($to)->firstOfMonth()->addDays(25)->format('Y-m-d');
    }

    static function genReportData($Name, $from, $to) {

      $from = DBHelper::dateShiftFrom($from);
      $to = DBHelper::dateShiftTo($to);
      Log::info('dateShift new from date ' . $from);
      Log::info('dateShift new to date ' . $to);


      $isAllMode = ('ALL' == $Name);
      //Log::info('挑課卡 getUserIdByUserName=' . DBHelper::getUserIdByUserName($Name));
      //挑課卡
      if ($isAllMode) {
          $purchase = DB::collection('Purchase')
              ->where('PaymentTime', '>=', DBHelper::parse($from))
              ->where('PaymentTime', '<', DBHelper::parse($to))
              ->get();
      } else {
          $purchase = DB::collection('Purchase')
              ->where('PaymentTime', '>=', DBHelper::parse($from))
              ->where('PaymentTime', '<', DBHelper::parse($to))
              ->where('UserID', DBHelper::getUserIdByUserName($Name))
              ->get();
      }

      //插入線上課程
      if ($isAllMode) {
        $purchase2 = DB::collection(DBHelperOnline::$CollectPurchase)
            ->where('PaymentTime', '>=', DBHelper::parse($from))
            ->where('PaymentTime', '<', DBHelper::parse($to))
            ->get();
      } else {
        $purchase2 = DB::collection(DBHelperOnline::$CollectPurchase)
            ->where('PaymentTime', '>=', DBHelper::parse($from))
            ->where('PaymentTime', '<', DBHelper::parse($to))
            ->where('UserID', DBHelper::getUserIdByUserName($Name))
            ->get();
      }



      $locationMap = DBHelper::getLocationMap();

      $totlaRecord = [];
      foreach ($purchase as $each) {
          $each['Type'] = DBHelper::$tableTypeAsana;
          $each['Name'] = DBHelper::getUserName($each['UserID']);
          $each['Location'] =  $locationMap[ $each['Name'] ] ?? null;
          array_push($totlaRecord, $each);
      }
      //插入線上課程
      foreach ($purchase2 as $each) {
          $each['Type'] = '線上體位法';
          $each['Name'] = DBHelper::getUserName($each['UserID']);
          $each['Location'] =  $locationMap[ $each['Name'] ] ?? null;
          array_push($totlaRecord, $each);
      }

      //挑讀書會
      $activity = DB::collection('Activity')
          ->get();
      foreach ($activity as $each) {
          $payday = DBHelper::parse($each[DBHelper::$tableColumnPayDay]);
          if (DBHelper::isInRange($payday, $from, $to)) {
              $inputName = $each[DBHelper::$tableColumnCnEnName];
              if ($isAllMode || $inputName == $Name) {
                  $each['Name'] = $inputName;
                  $each['Payment'] = $each[DBHelper::$tableColumnAmount];
                  $each['PaymentTime'] =  $payday;
                  $each['Type'] = DBHelper::$tableTypeStudyGroup;
                  $each['Location'] = $locationMap[ $each['Name'] ]?? null;
                  array_push($totlaRecord, $each);
              }
          }
      }

      //挑體位法補兩百的
      $activity = DB::collection('AsanaExtend')
          ->get();

          foreach ($activity as $each) {
              $payday = DBHelper::parse($each['日期']);
              if (DBHelper::isInRange($payday, $from, $to)) {
                  $inputName = $each['學員'];
                  if ($isAllMode || $inputName == $Name) {
                      $each['Name'] = $inputName;
                      $each['Payment'] = '200';
                      $each['PaymentTime'] =  $payday;
                      $each['Type'] = '補兩百';
                      $each['Location'] = $locationMap[ $each['Name'] ]?? null;
                      array_push($totlaRecord, $each);
                  }
              }
          }

          //冥想會，台中
          $activity = DB::collection('OtherActivity')
              ->get();

              foreach ($activity as $each) {
                  $payday = DBHelper::parse($each['日期']);
                  if (DBHelper::isInRange($payday, $from, $to)) {
                      $inputName = $each['姓名'];
                      if ($isAllMode || $inputName == $Name) {
                          $each['Name'] = $inputName;
                          $each['Payment'] = $each['金額'];
                          $each['PaymentTime'] =  $payday;
                          $each['Type'] = $each['事由'];
                          $each['Location'] = $locationMap[ $each['Name'] ]?? null;
                          array_push($totlaRecord, $each);
                      }
                  }
              }

              //讀書會
              $activity = DB::collection('StudyGroup')
                  ->get();

                  foreach ($activity as $each) {
                      $payday = DBHelper::parse($each['日期']);
                      if (DBHelper::isInRange($payday, $from, $to)) {
                          $inputName = $each['中文全名/英文名'];
                          if ($isAllMode || $inputName == $Name) {
                              $each['Name'] = $inputName;
                              $each['Payment'] = $each['費用總計'];
                              $each['PaymentTime'] =  $payday;
                              $each['Type'] = '讀書會';
                              $each['Location'] = $locationMap[ $each['Name'] ]?? null;
                              array_push($totlaRecord, $each);
                          }
                      }
                  }

        return $totlaRecord;
    }

    public static function sortByName($totlaRecord) {
      usort($totlaRecord, function ($item1, $item2) {
        return $item1['Name'] <=> $item2['Name'];
      });

      return $totlaRecord;
    }

    public static function sortByType($totlaRecord) {
      usort($totlaRecord, function ($item1, $item2) {
        $rdiff = $item1['Type'] <=> $item2['Type'];
        if ($rdiff) return $rdiff;

        $rdiff = $item1['Location'] <=> $item2['Location'];
        if ($rdiff) return $rdiff;

        $rdiff = DBHelper::toMonth($item1['PaymentTime']) <=> DBHelper::toMonth($item2['PaymentTime']);
        return $rdiff;
      });

      return $totlaRecord;
    }

    // public static function getBalanceIn2($userId, $from, $to)
    // {
    //     return DB::collection('Purchase')
    //         ->where('UserID', $userId)
    //         ->where('PaymentTime', '>=', DBHelper::parse($from))
    //         ->where('PaymentTime', '<', DBHelper::parse($to))
    //         ->get();
    // }

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
        if($user==null)
            return "無此使用者";
        if ($user['UserName'] != '')
            return $user['UserName'];
        else
            return $user['NickName'];
    }

    public static function getUserLocation($userId)
    {
        $user = DB::collection('UserInfo')->where('UserID', $userId)->first();
        if($user==null)
            return "N/A";
        if ($user['Location'] == '')
            return "N/A";
        else
            return $user['Location'];
    }

    public static function getNickName($userId)
    {
        return DB::collection('UserInfo')->where('UserID', $userId)->first()['NickName'];
    }

    public static function getUserIdByUserName($Name)
    {
        $user = DB::collection('UserInfo')->where('UserName', $Name)->first();
        if ($user != null)
            return $user['UserID'];
        else
            return '';
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

    public static function getPersonalIDMap()
    {
        $result =  DB::collection('UserInfo')
            ->select('PersonalID', 'UserName')
            ->get();
        $map = array();
        foreach ($result as $row) {
            $map[$row['UserName']] = $row['PersonalID'];
        }
        //Log::info('getPersonalIDMap=' . $map);
        return $map;
    }

    public static function getLocationMap()
    {
        $result =  DB::collection('UserInfo')
            ->select('Location', 'UserName')
            ->get();
        $map = array();
        foreach ($result as $row) {
          if(array_key_exists('Location', $row)) {
            $map[$row['UserName']] = $row['Location'];
          }
        }
        //Log::info('getPersonalIDMap=' . $map);
        return $map;
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
                "UserName" => $user_profile['displayName'],
                "Email" => $user_profile['email'] ?? "",
                "PictureUrl" => $user_profile['pictureUrl'],
                "Mobile" => "",
                "Address" => "",
                "Referrer" => "",
                "PersonalID" => "",
            ]);
    }

    public static function buyNewCard($userId, $point)
    {
        $cardId = DBHelper::getCardId();
        if ($point == 1)
            DBHelper::insertPurchaseNoExpired($cardId, $userId, 500, $point);
        else
            DBHelper::insertPurchase($cardId, $userId, 1800, $point);
        return $cardId;
    }

    public static function clearPoints($cardId)
    {
        $newdata = array('$set' => array(
            'Points' => 0,
        ));
        DB::collection('Purchase')
            ->where('CardID', $cardId)
            ->where('Points', ">", 0)
            ->update($newdata);
    }

    //逾期補差額:舊卡點數轉移至新卡
    // public static function extendCard($userId, $cardId)
    // {
    //     //$cardId = base64_decode($cardId);//不是給網頁call，不用de_base6
    //     $card = DBHelper::getCard($cardId);
    //     $point = $card['Points'];

    //     //清空點數
    //     DBHelper::clearPoints($cardId);

    //     $amount = 200;
    //     DBHelper::insertPurchaseNoExpired($userId, $amount, $point);
    // }

    //逾期補差額: Payment 1800變2000，期限變無限期，更新PaymentTime
    // public static function extendCard($cardId)
    // {
    //     $newdata = array('$set' => array(
    //         'Payment' => 2000,
    //         'Expired' => null,
    //         'PaymentTime' => DBHelper::getMongoDateNow(),
    //     ));

    //     Log::info('extendCard(' . $cardId . ') =' . json_encode($newdata));
    //     DB::collection('Purchase')
    //         ->where('CardID', $cardId)
    //         ->update($newdata);
    // }

    //逾期補差額v3: 新增一筆200的卡片，CardID與原卡相同，點數為零，無限期。原卡的期限變無限期
    public static function extendCard($cardId)
    {
        //新增一筆200的卡片
        DBHelper::insertPurchaseNoExpired($cardId, DBHelper::getUserId($cardId), 200, 0);

        //更新原卡
        $newdata = array('$set' => array(
            'Expired' => null,
        ));

        Log::info('extendCard更新原卡(' . $cardId . ') =' . json_encode($newdata));
        DB::collection('Purchase')
            ->where('CardID', $cardId)
            ->update($newdata);
    }

    public static function insertPurchase($cardId, $id, $amount, $point)
    {
        $dt = DBHelper::getMongoDateNow();
        $expired_at = Carbon::now()->add(60, 'day')->toDateTimeString();
        $dt_expired = DBHelper::strtoMongoDate($expired_at);

        $newCard = [
            'CardID' => $cardId,
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

    public static function insertPurchaseNoExpired($cardId, $userId, $amount, $point)
    {
        $dt = DBHelper::getMongoDateNow();
        $newCard = [
            'CardID' => $cardId,
            'UserID' => $userId,
            'Points' => (int)$point,
            "Expired" => null,
            "CardCreateTime" => $dt,
            'Payment' => $amount,
            'PaymentTime' => $dt,
        ];

        Log::info('insertPurchaseNoExpired =' . json_encode($newCard));

        DB::collection('Purchase')
            ->insert($newCard);
    }

    //退款
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

    //該卡號有負數的紀錄的話
    public static function isRefundable($cardId)
    {
        $datas = DB::collection('Purchase')
            ->where('CardID', $cardId)
            ->where('Payment', '<', 0) //退卡的
            ->get();
        //Log::info('DBHelper::isRefundable size(Payment<0)=' . sizeof($datas));
        if (sizeof($datas) > 0)
            return true;
        else
            return false;
    }

    public static function isTodayDone($cardId)
    {
        $registArray = DBHelper::getConsume($cardId);
        $today = DBHelper::todaySlash();
        $isTodayDone = false; //今日蓋過不可再蓋
        for ($i = 0; $i < sizeof($registArray); $i++) {
            if ($today == DBHelper::toDateString($registArray[$i]['PointConsumeTime'])) {
                $isTodayDone = true;
                break;
            }
        }
        return $isTodayDone;
    }
}
