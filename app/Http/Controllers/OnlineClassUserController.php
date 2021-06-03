<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\DBHelperOnline as DBHelperOnline;
use Carbon\Carbon;

class OnlineClassUserController extends Controller
{
    //protected $classcardService;

    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function history()
    {
        $userId = null;
        if (isset($_COOKIE["userId"])) {
            $userId = $_COOKIE["userId"];
        } else {
            return app('App\Http\Controllers\LoginController')->onlineclassLogin();
        }
        return $this->historyPick($userId, 0);
    }

    public function historyPick($userId, $index)
    {
        $arr = DBHelperOnline::getOnlineHistory($userId);
        //Log::info("showClassHistory({$userId},index={$index}) data={$arr} ");
        if (count($arr) == 0) {
            print_r('<h3>無上課紀錄</h3>');
            return;
        }

        if ($index >= sizeof($arr)) $index = sizeof($arr) - 1;
        return view('onlineHistory', [
            'card' => $arr[$index],
            'index' => $index
        ]);
    }

}
