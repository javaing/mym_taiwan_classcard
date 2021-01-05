<?php

namespace App\Http\Controllers;

require '..//vendor//autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\DBHelper as DBHelper;
use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    private function getLastMonthRange()
    {
        return Carbon::now()->month % 2 == 0 ? -1 : -2; //上個區間;
    }

    public function balance2(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        //Log::info($start);
        //Log::info($end);
        if (!$start) {
            $index = $this->getLastMonthRange(); //上個區間;
            $start = Carbon::now()->startOfMonth()->add($index, 'month');
            $end = Carbon::now()->startOfMonth()->add($index + 2, 'month')->add(-1, 'day');
        }

        return view('balance2', [
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function balance2post(Request $request)
    {
        $range = $request->range;
        $startMonth = $this->getLastMonthRange(); //上個區間;
        $endMonth = $startMonth + 2;
        if (!$range) { //上個區間;
        } else if ($range == 7) { //去年十一十二月
            $startMonth = $range * 2 - 2 - 14;
            $endMonth = $range * 2 - 14;
        } else {
            $startMonth = $range * 2 - 2;
            $endMonth = $range * 2;
        }
        $start = Carbon::now()->startOfYear()->add($startMonth, 'month');
        $end = Carbon::now()->startOfYear()->add($endMonth, 'month')->add(-1, 'day');


        return view('balance2', [
            'start' => $start,
            'end' => $end,
            'range' => $range
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

    //查某個人日期區間內的繳款
    public function cardDetail2(Request $request)
    {
        $userId = $request->userId;
        if (!$userId) {
            print_r('無法辨識使用者');
            return;
        }
        $start = $request->start;
        $end = $request->end;
        $paidArray = DBHelper::getBalanceIn2($userId, $start, $end);
        Log::info("cardDetail2.paidArray({$paidArray})");
        return view('balanceDetail2', [
            'paidArray' => $paidArray,
            'start' => $start,
            'end' => $end,
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


    //save data to excel, then download excel
    public function downloadFile(Request $request)
    {
        $file = $request->filename;
        $file = "Asana付款紀錄_" . $file . "_mymtw.xlsx";
        $start = $request->start;
        $end = $request->end;
        $userId = $request->userId;

        if ($userId) {
            $arrIn = DBHelper::getBalanceIn2($userId, $start, $end);
        } else {
            $arrIn = DBHelper::getBalanceIn($start, $end);
        }
        $map = DBHelper::getPersonalIDMap();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', '名字');
        $sheet->setCellValue('B1', '日期');
        $sheet->setCellValue('C1', '金額');
        $sheet->setCellValue('D1', '身分證');
        for ($i = 0; $i < sizeof($arrIn); $i++) {
            $j = $i + 2;
            $sheet->setCellValue('A' . $j, DBHelper::getUserName($arrIn[$i]['UserID']));
            $sheet->setCellValue('B' . $j, DBHelper::toDateStringShort($arrIn[$i]['PaymentTime']));
            $sheet->setCellValue('C' . $j,  number_format($arrIn[$i]['Payment']));
            $sheet->setCellValue('D' . $j, $map[$arrIn[$i]['UserID']]);
        }


        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($file);


        $path = "..\\public\\" . $file;
        return response()->download($path, $file);
    }
}
