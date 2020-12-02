<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\DBHelper as DBHelper;

class ClassCardController extends Controller
{
    //protected $classcardService;

    public function __construct()
    {
        //$this->classcardService = $classcardService;
    }


    public function registeclassByPoint($point, $cardId)
    {
        //先檢查一天只能蓋一次
        $exist = DBHelper::isConsume($cardId, $point);
        if ($exist) {
            $link = $_SERVER['HTTP_REFERER'];
            print_r('<h3>今日已蓋章，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }

        //扣點數
        DBHelper::registeclassByPoint($cardId, $point);
        //紀錄花費500 or 300
        DBHelper::insertConsumeToday($cardId, $point);
        return redirect('classcard/show/' . $cardId);
    }

    public function extendCard(Request $request)
    {
        $userId = $request->userId;
        $cardId = $request->cardId;
        //Log::info("extendCard({$userId},cardId={$cardId})");
        DBHelper::extendCard($userId, $cardId);

        $card = DBHelper::getValidCard($userId);
        return redirect('classcard/show/' . $card['CardID']);
    }


    public function buyClassCard(Request $request)
    {
        $userId = $request->userId;
        $point = $request->point; //1 or 4
        DBHelper::buyNewCard($userId, $point);

        $card = DBHelper::getValidCard($userId);
        return redirect('classcard/show/' . $card['CardID']);
    }

    public function showClassCard($cardId)
    {
        $card = DBHelper::getCard($cardId);
        if (!$card) {
            $link = $_SERVER['HTTP_REFERER'];
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
        if (!$arr) {
            $link = $_SERVER['HTTP_REFERER'];
            print_r('<h3>無上課紀錄，請<a href="' . $link . '">回上頁</a></h3>');
            return;
        }
        //Log::info("showClassHistory({$userId},index={$index})");
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
