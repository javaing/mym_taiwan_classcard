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
        Log::info('onlineclass.cardDetail.open', [
            'encodedCardId' => $request->cardId,
            'cardId' => $cardId,
            'found' => $card != null,
            'referer' => $request->headers->get('referer'),
        ]);
        if (!$card) {
            print_r('無此課卡');
            return;
        }
        //Log::info("cardId({$cardid})");
        return view('onlineclassDetail', [
            'cardId' => $cardId,
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

        if ($onlineCard['Points'] <= 0) {
            Log::warning("onlineclass registeByhand blocked: no points. cardId={$cardId}");
            $link = $this->goBackLink();
            print_r('<h3>此卡已無可用點數，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        $dt = $request->registedate;
        //Log::info("registeclassByhand dt($dt)");
        $timezone_ms = 8 * 60 * 60 * 1000;
        $dt = new \MongoDB\BSON\UTCDateTime(strtotime($dt) * 1000 + $timezone_ms);

        //先檢查該日期是否已蓋過章
        $exist = DBHelperOnline::isConsumeByDate($cardId, $dt);
        if ($exist) {
            Log::warning("onlineclass registeByhand blocked: already consumed that date. cardId={$cardId}");
            $link = $this->goBackLink();
            print_r('<h3>今日已蓋章，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        //扣點數(compare-and-swap，避免點數已用完仍被手動補登蓋過頭)
        $currentPoints = $onlineCard['Points'];
        if (!DBHelperOnline::tryConsumePoint($cardId, $currentPoints)) {
            Log::warning("onlineclass registeByhand blocked: CAS conflict. cardId={$cardId}, expectedPoints={$currentPoints}");
            $link = $this->goBackLink();
            print_r('<h3>資料已被更新，請<a href="' . $link . '">回上頁</a>後重新整理再試</h3>');
            return;
        }

        Log::info("onlineclass registeByhand success. cardId={$cardId}, points {$currentPoints}->" . ($currentPoints - 1));
        //紀錄花費500 or 300
        DBHelperOnline::insertConsume($cardId, $currentPoints, $dt);
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
