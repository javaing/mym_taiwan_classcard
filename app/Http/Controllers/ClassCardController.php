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
            print_r('今日已蓋章，請<a href="' . $link . '">回上頁</a>');
            return;
        }

        //扣點數
        DBHelper::registeclassByPoint($cardId, $point);
        //紀錄花費500 or 300
        DBHelper::insertConsume($cardId, $point);
        return redirect('classcard/' . $cardId);
    }

    public function buyClassCard(Request $request)
    {
        $userId = $request->userId;
        DBHelper::buyNewCard($userId);

        $card = DBHelper::getValidCard($userId);
        return redirect('classcard/' . $card['CardID']);
    }

    public function showClassCard($cardId)
    {
        $card = DBHelper::getCard($cardId);
        if (!$card) {
            $link = $_SERVER['HTTP_REFERER'];
            print_r('無此課卡，請<a href="' . $link . '">回上頁</a>');
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
            print_r('無上課紀錄，請<a href="' . $link . '">回上頁</a>');
            return;
        }
        Log::info("showClassHistory({$userId},index={$index})");
        return view('classhistory', [
            'card' => $arr[$index],
            'index' => $index
        ]);
    }
}
