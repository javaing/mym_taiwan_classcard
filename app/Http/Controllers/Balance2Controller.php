<?php

namespace App\Http\Controllers;

require '..//vendor//autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\DBHelper as DBHelper;
use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Balance2Controller extends Controller
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

    //查某個人日期區間內的繳款
    public function cardDetail2(Request $request)
    {
        $userName = $request->userName;
        if (!$userName) {
            print_r('無法辨識使用者');
            return;
        }
        Log::info("cardDetail2.userName({$userName})");
        $start = $request->start;
        $end = $request->end;
        $paidArray = DBHelper::getBalanceInJoin($userName, $start, $end);

        return view('balanceDetail2', [
            'paidArray' => $paidArray,
            'start' => $start,
            'end' => $end,
        ]);
    }

    //save data to excel, then download excel
    public function downloadFile(Request $request)
    {
        $file = $request->filename;
        $file = "Asana付款紀錄_" . $file . "_mymtw.xlsx";
        $start = $request->start;
        $end = $request->end;
        $userName = $request->userName;


        // if ($userName) {
        //     $arrIn = DBHelper::getBalanceIn2($userName ?: 'ALL', $start, $end);
        // } else {
        //     $arrIn = DBHelper::getBalanceIn2('ALL', $start, $end);
        // }
        $arrIn = DBHelper::getBalanceInJoin($userName ?: 'ALL', $start, $end);


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', '名字');
        $sheet->setCellValue('B1', '日期');
        $sheet->setCellValue('C1', '金額');
        $sheet->setCellValue('D1', '種類');
        for ($i = 0; $i < sizeof($arrIn); $i++) {
            $j = $i + 2;
            $sheet->setCellValue('A' . $j, $arrIn[$i]['Name']);
            $sheet->setCellValue('B' . $j, DBHelper::toDateStringShort($arrIn[$i]['PaymentTime']));
            $sheet->setCellValue('C' . $j,  number_format($arrIn[$i]['Payment']));
            $sheet->setCellValue('D' . $j, $arrIn[$i]['Type']);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($file);

        //$path = "..\\public\\" . $file;
        $path = "../public/" . $file; //這個寫法windows, ubuntu都可接受
        return response()->download($path, $file);
    }
}
