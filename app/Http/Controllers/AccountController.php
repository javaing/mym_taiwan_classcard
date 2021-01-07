<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\DBHelper as DBHelper;
use Carbon\Carbon;

class AccountController extends Controller
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

    public function create()
    {
        $start = Carbon::now()->startOfMonth()->add(-1, 'month');
        $end = Carbon::now()->startOfMonth()->add(1, 'month');

        $request = new Request();
        $request->start = DBHelper::toDateString($start);
        $request->end = DBHelper::toDateString($end);
        return $this->balance($request);
    }

    public function balance(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        //Log::info($start);
        //Log::info($end);
        if (!$start) {
            $start = Carbon::now()->startOfMonth()->add(-1, 'month');
            $end = Carbon::now()->startOfMonth()->add(1, 'month');
        }

        return view('balance', [
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function cardDetail($cardId)
    {
        $cardId = base64_decode($cardId);
        $card = DBHelper::getCard($cardId);
        if (!$card) {
            print_r('無此課卡');
            return;
        }
        //Log::info("cardId({$cardid})");
        return view('balanceDetail', [
            'cardId' => $cardId,
        ]);
    }


    public function deposite(Request $request)
    {
        $cardId = base64_decode($request->cardId);
        $amount = $request->amount;
        Log::info("deposite($cardId, $amount)");
        $exist = DBHelper::isDeposited($cardId);
        //Log::info("deposite($exist)");
        //print_r($exist);
        //return;

        $backLink = $this->goBackLink();
        if ($exist) {
            print_r('資料已重複不予處理，請<a href="' . $backLink . '">回上頁</a>');
        } else if ($cardId == null) {
            print_r('無卡號無法退款，請<a href="' . $backLink . '">回上頁</a>');
        } else {
            DBHelper::refund($cardId, $amount);
            return view('balanceDetail', [
                'cardId' => $cardId,
            ]);
        }
    }

    //for get
    public function classByhand()
    {
        return view('classbyhand');
    }

    //for post
    public function registeclassByhand(Request $request)
    {
        $cardId = base64_decode($request->cardId);
        //Log::info("registeclassByhand cardId($cardId)");
        if (DBHelper::getCard($cardId) == null) {
            $link = $this->goBackLink();
            print_r('<h3>尚未選卡，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }


        $point = DBHelper::getCard($cardId)['Points']; //1 or 4
        $dt = $request->registedate;
        //Log::info("registeclassByhand dt($dt)");
        $timezone_ms = 8 * 60 * 60 * 1000;
        $dt = new \MongoDB\BSON\UTCDateTime(strtotime($dt) * 1000 + $timezone_ms);


        //先檢查一天只能蓋一次
        $exist = DBHelper::isConsumeByDate($cardId, $dt);
        if ($exist) {
            $link = $this->goBackLink();
            print_r('<h3>今日已蓋章，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        //扣點數
        DBHelper::registeclassByPoint($cardId, $point);
        //紀錄花費500 or 300
        DBHelper::insertConsume($cardId, $point, $dt);
        return redirect('account/carddetail' . $cardId);
    }

    public function balanceByUser($userId)
    {
        if ($userId == null)
            $userId = $_COOKIE["userId"];
        return view('balancebyuser', [
            'userId' => $userId,
        ]);
    }
}
