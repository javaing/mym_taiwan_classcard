<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\DBHelper as DBHelper;
use App\Helpers\Tools as Tools;
use Illuminate\Support\Facades\Auth;

class ClassCardController extends Controller
{
    public function __construct()
    {
    }

    public function goBackLink()
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }


    public function registeclassByPoint($point, $cardId)
    {
        $cardId = base64_decode($cardId);
        //先檢查一天只能蓋一次
        $exist = DBHelper::isConsume($cardId, $point);
        if ($exist) {
            $link = $this->goBackLink();
            print_r('<h3>今日已蓋章，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        //扣點數
        DBHelper::registeclassByPoint($cardId, $point);
        //紀錄花費500 or 300
        DBHelper::insertConsumeToday($cardId, $point);
        return redirect('classcard/show/' . base64_encode($cardId));
    }

    public function extendCard(Request $request)
    {
        $userId = $request->userId;
        $cardId = $request->cardId;
        $cardId = base64_decode($cardId);
        Log::info("extendCard({$userId},cardId={$cardId})");

        //是不是有需展期的卡片
        $card = DBHelper::getCard($cardId);
        if (!DBHelper::isExpired($card)) {
            $link = $this->goBackLink();
            print_r('<h3>無資料或此卡尚未逾期，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        DBHelper::extendCard($cardId);
        return redirect('classcard/show/' . base64_encode($cardId));
    }


    public function buyClassCard(Request $request)
    {
        $userId = $request->userId;
        $point = $request->point; //1 or 4

        //是不是還有未使用的卡片
        $card = DBHelper::getValidCard($userId);
        if ($card) {
            $link = $this->goBackLink();
            print_r('<h3>還有未使用的卡片(卡號:' . $card['CardID'] . ')，不須買新卡，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }
        if ($userId==null || $userId=="null") {
            $link = $this->goBackLink();
            print_r('<h3>無使用者資訊，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }
        $cardId = DBHelper::buyNewCard($userId, $point);
        Log::info("buyClassCard({$cardId}, {$point})");
        return redirect('classcard/show/' . base64_encode($cardId));
    }

    public function buyClassCardPost(Request $request)
    {
        $userId = $request->userId;
        $point = $request->point; //1 or 4
        $buycardPass = strtolower($request->buycardPass);

        if ($userId==null || $userId=="null") {
            $link = $this->goBackLink();
            print_r('<h3>無使用者資訊，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        if($buycardPass=='') {
          $link = $this->goBackLink();
          print_r('<h3>需輸入買卡密碼，請洽工作人員<a href="' . $link . '">回上頁</a></h3>');
          return;
        }

        //Log::info("buyClassCard check pass {$buycardPass}, ".Tools::getBuyCardPassword()      );
        if($buycardPass != Tools::getBuyCardPassword() && $buycardPass != config('line.buy_newcard_pass')) {
          $link = $this->goBackLink();
          print_r('<h3>買卡密碼不正確，請洽工作人員<a href="' . $link . '">回上頁</a></h3>');
          return;
        }


        //是不是還有未使用的卡片
        $card = DBHelper::getValidCard($userId);
        if ($card) {
            $link = $this->goBackLink();
            print_r('<h3>還有未使用的卡片(卡號:' . $card['CardID'] . ')，不須買新卡，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }
        $cardId = DBHelper::buyNewCard($userId, $point);
        Log::info("buyClassCard({$cardId}, {$point})");
        return redirect('classcard/show/' . base64_encode($cardId));
    }

    public function buyNewCardView(Request $request) {
      return view("buynewcard")->with('userId', $request->userId);
    }

    public function buyCardPass() {
      if (Auth::check()) {
        //echo '<H3>購卡密碼是['. Tools::getBuyCardPassword() .']</H3>';
        return view("classcardpass")->with('pass', Tools::getBuyCardPassword());
      } else {
        return redirect('login');
      }

    }


    public function showClassCard($cardId)
    {
        $cardId = base64_decode($cardId);
        $card = DBHelper::getCard($cardId);
        if (!$card) {
            $link = $this->goBackLink();
            print_r('<h3>無此課卡，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }
        Log::info("showClassCard({$cardId})");
        return view('classcard', [
            'card' => $card,
        ]);
    }

    public function showClassHistory($userId, $index)
    {
        $arr = DBHelper::getUserHistory($userId);
        Log::info("showClassHistory({$userId},index={$index}) data={$arr} ");
        if (count($arr) == 0) {
            $link = $this->goBackLink();
            print_r('<h3>無上課紀錄，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        if ($index >= sizeof($arr)) $index = sizeof($arr) - 1;
        return view('classhistory', [
            'card' => $arr[$index],
            'index' => $index
        ]);
    }

    public function showClassHistoryByCookie()
    {
        $userId = null;
        if (isset($_COOKIE["userId"])) {
            $userId = $_COOKIE["userId"];
        } else {
            return app('App\Http\Controllers\LoginController')->pageLine();
        }
        return $this->showClassHistory($userId, 0);
    }


}
