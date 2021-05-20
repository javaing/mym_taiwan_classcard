<?php

namespace App\Http\Controllers;

require '..//vendor//autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\DBHelper as DBHelper;
use App\Helpers\Tools as Tools;
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
        $file = "MYMTW_活動收費紀錄_" . $file . ".xlsx";
        $start = $request->start;
        $end = $request->end;
        $userName = $request->userName;


        $arrIn = DBHelper::getBalanceInJoin($userName ?: 'ALL', $start, $end);

        return $this->genFile($arrIn, $file);
    }

    public function genFile($arrIn, $file) {
      //$pidMap = DBHelper::getPersonalIDMap();

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setCellValue('A1', '名字');
      //$sheet->setCellValue('B1', '身分證號');
      $sheet->setCellValue('B1', '日期');
      $sheet->setCellValue('C1', '金額');
      $sheet->setCellValue('D1', '種類');
      $sheet->setCellValue('E1', '所在');
      for ($i = 0; $i < sizeof($arrIn); $i++) {
          $j = $i + 2;
          $name  = $arrIn[$i]['Name'];
          $sheet->setCellValue('A' . $j, $name);
          //$sheet->setCellValue('B' . $j, array_key_exists( $name, $pidMap)?  $pidMap[ $name ] : ''   );
          $sheet->setCellValue('B' . $j, DBHelper::toDateStringShort($arrIn[$i]['PaymentTime']));
          $sheet->setCellValue('C' . $j,  number_format($arrIn[$i]['Payment']));
          $sheet->setCellValue('D' . $j, $arrIn[$i]['Type']);
          $sheet->setCellValue('E' . $j, $arrIn[$i]['Location']);
      }


      // //$spreadsheet->createSheet();
      // //$spreadsheet->createSheet();
      // $sheet = $spreadsheet->setActiveSheetIndex(1);
      // $spreadsheet->getActiveSheet()->setTitle('台中');
      // $sheet->setCellValue('A1', '名字台中');
      // //$sheet->setCellValue('B1', '身分證號');
      // $sheet->setCellValue('B1', '日期台中');
      // $sheet->setCellValue('C1', '金額台中');
      // $sheet->setCellValue('D1', '種類台中');
      // for ($i = 0; $i < sizeof($arrIn); $i++) {
      //     $j = $i + 2;
      //     $name  = $arrIn[$i]['Name'];
      //     $sheet->setCellValue('A' . $j, $name+"台中");
      //     //$sheet->setCellValue('B' . $j, array_key_exists( $name, $pidMap)?  $pidMap[ $name ] : ''   );
      //     $sheet->setCellValue('B' . $j, DBHelper::toDateStringShort($arrIn[$i]['PaymentTime']));
      //     $sheet->setCellValue('C' . $j,  number_format($arrIn[$i]['Payment']));
      //     $sheet->setCellValue('D' . $j, $arrIn[$i]['Type']);
      // }
      //
      // $clonedWorksheet = clone $spreadsheet->getSheetByName('Worksheet 1');
      // $clonedWorksheet->setTitle('台中');
      // $spreadsheet->addSheet($clonedWorksheet);


      $writer = new Xlsx($spreadsheet);
      $writer->save($file);

      //$path = "..\\public\\" . $file;
      $path = "../public/" . $file; //這個寫法windows, ubuntu都可接受
      return response()->download($path, $file);
    }


    //save data to excel, then download excel
    public function downloadFileGroupByname(Request $request)
    {
        $file = $request->filename;
        $file = "MYMTW_活動收費紀錄_" . $file . "_byName.xlsx";
        $start = $request->start;
        $end = $request->end;
        $userName = $request->userName;


        $arrIn = DBHelper::getBalanceInJoin($userName ?: 'ALL', $start, $end);

        $groupBy = array();
        $amountName = 'Payment';
        $typeName = 'Type';
        foreach ($arrIn as $element) {
          //$newElement = array_search( $element['Name'], $groupBy);
          //Log::info('sizeof='.sizeof($groupBy).'  name='.$element['Name'] );

          //groupBy裡有舊資料, 則更新
          $nameExist = false;
          foreach ($groupBy as &$eachPerson) {
            if($eachPerson['Name'] === $element['Name']) {
              $eachPerson[$amountName] = $eachPerson[$amountName] + $element[$amountName];
              $eachPerson[$typeName] = $eachPerson[$typeName] .', '. $element[$typeName];
              $nameExist = true;
              break;
            }
          }

          if(!$nameExist) {
            array_push($groupBy, $element);
          }
        }
        $arrIn = $groupBy;

        return $this->genFile($arrIn, $file);
    }

    public function downloadFileGroupByKind(Request $request)
    {
        $file = $request->filename;
        $file = "MYMTW_活動收費紀錄_" . $file . "_byKind.xlsx";
        $start = $request->start;
        $end = $request->end;
        $userName = $request->userName;


        $arrIn = DBHelper::getBalanceInJoinByType($userName ?: 'ALL', $start, $end);

        // $groupBy = array();
        // $amountName = 'Payment';
        // $typeName = 'Type';
        // foreach ($arrIn as $element) {
        //   //$newElement = array_search( $element['Name'], $groupBy);
        //   Log::info('sizeof='.sizeof($groupBy).'  name='.$element['Name'] );
        //
        //   //groupBy裡有舊資料, 則更新
        //   $nameExist = false;
        //   foreach ($groupBy as &$eachPerson) {
        //     if($eachPerson['Name'] === $element['Name']) {
        //       $eachPerson[$amountName] = $eachPerson[$amountName] + $element[$amountName];
        //       $eachPerson[$typeName] = $eachPerson[$typeName] .', '. $element[$typeName];
        //       $nameExist = true;
        //       break;
        //     }
        //   }
        //
        //   if(!$nameExist) {
        //     array_push($groupBy, $element);
        //   }
        // }
        // $arrIn = $groupBy;

        return $this->genFile($arrIn, $file);
    }

}
