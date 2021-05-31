<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\DBHelperOnline as DBHelperOnline;
use Carbon\Carbon;

class OnlineClassController extends Controller
{
    //protected $classcardService;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function goBackLink()
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    public function list() {
      return view('onlineclassList');
    }

    //for post
    public function buy(Request $request)
    {
        $userId = base64_decode($request->userId);
        $buydate = $request->buydate;
        $point = $request->point; //1 or 4

        $link = $this->goBackLink();

        if (!$userId) {
            print_r('<h3>請選擇購卡人<a href="' . $link . '">回上頁</a></h3>');
            return;
        }


        //是不是還有未使用的卡片
        $card = DBHelperOnline::hasValidCard($userId);
        if ($card) {
            $link = $this->goBackLink();
            print_r('<h3>還有未使用的卡片，不須買新卡，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }
        $cardId = DBHelperOnline::buyNewCard($userId, $point, $buydate);
        Log::info("buyClassCard({$cardId}, {$point})");
        return view('onlineclassList');
    }

    public function cardDetail(Request $request)
    {
        $cardId = base64_decode($request->cardId);
        $card = DBHelperOnline::getCardOnline($cardId);
        if (!$card) {
            print_r('無此課卡');
            return;
        }
        //Log::info("cardId({$cardid})");
        return view('onlineclassDetail', [
            'cardId' => $cardId,
        ]);
    }

    public function history()
    {
        $userId = null;
        if (isset($_COOKIE["userId"])) {
            $userId = $_COOKIE["userId"];
        } else {
            return app('App\Http\Controllers\LoginController')->pageLine();
        }
        return $this->historyPick($userId, 0);
    }

    public function historyPick($userId, $index)
    {
        $arr = DBHelperOnline::getOnlineHistory($userId);
        //Log::info("showClassHistory({$userId},index={$index}) data={$arr} ");
        if (count($arr) == 0) {
            $link = $this->goBackLink();
            print_r('<h3>無上課紀錄，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        if ($index >= sizeof($arr)) $index = sizeof($arr) - 1;
        return view('onlinehistory', [
            'card' => $arr[$index],
            'index' => $index
        ]);
    }

    //listByhand
    public function listByhand() {
      return view('onlineclassByhand');
    }

    //for post
    public function registeByhand(Request $request)
    {
        $cardId = base64_decode($request->cardId);
        $onlineCard = DBHelperOnline::getCardOnline($cardId);
        //Log::info("registeclassByhand cardId($cardId)");
        if ($onlineCard == null) {
            $link = $this->goBackLink();
            print_r('<h3>尚未選卡，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        $point = $onlineCard['Points']; //1 or 4
        $dt = $request->registedate;
        //Log::info("registeclassByhand dt($dt)");
        $timezone_ms = 8 * 60 * 60 * 1000;
        $dt = new \MongoDB\BSON\UTCDateTime(strtotime($dt) * 1000 + $timezone_ms);

        //先檢查一天只能蓋一次
        $exist = DBHelperOnline::isConsumeByDate($cardId, $dt);
        if ($exist) {
            $link = $this->goBackLink();
            print_r('<h3>今日已蓋章，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        //扣點數
        DBHelperOnline::registeByPoint($cardId, $point);
        //紀錄花費500 or 300
        DBHelperOnline::insertConsume($cardId, $point, $dt);
        //return redirect('account/carddetail/' . base64_encode($cardId));
        return view('onlineclassByhand');
    }

    public function refund(Request $request)
    {
        $cardId = base64_decode($request->cardId);
        $amount = $request->amount;
        $exist = DBHelperOnline::isRefundable($cardId);

        $backLink = $this->goBackLink();
        if ($exist) {
            print_r('資料已重複不予處理，請<a href="' . $backLink . '">回上頁</a>');
        } else if ($cardId == null) {
            print_r('無卡號無法退款，請<a href="' . $backLink . '">回上頁</a>');
        } else {
            DBHelperOnline::refund($cardId, $amount);
            return view('onlineclassDetail', [
                'cardId' => $cardId,
            ]);
        }
    }




}
