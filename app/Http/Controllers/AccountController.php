<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
//use App\Services\ClassCardService;
use App\Helpers\DBHelper as DBHelper;
use Carbon\Carbon;

class AccountController extends Controller
{
    //protected $classcardService;

    public function __construct()
    {
        //$this->classcardService = $classcardService;
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
        $card = DBHelper::getCard($cardId);
        if (!$card) {
            print_r('無此課卡');
            return;
        }
        Log::info("cardId({$cardId})");
        return view('balanceDetail', [
            'cardId' => $cardId,
        ]);
    }
}
