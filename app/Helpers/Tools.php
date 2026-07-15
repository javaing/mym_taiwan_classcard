<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;


class Tools
{

  /**
   * 產生「當日購卡密碼」，以固定密碼字串加上日期做雜湊，取前4字元。
   * 改用 md5 而非 srand()+rand()，避免依賴 PHP/OS 底層 rand 實作差異，
   * 確保同一天在任何時間、任何伺服器上都能得到一致的結果。
   */
  public static function getBuyCardPassword() {
    $secret = config('line.buy_newcard_pass', 'bhakti');
    return substr(md5($secret . date('Ymd')), 0, 4);
  }

  public static function _group_by($array, $key) {
      $return = array();
      foreach($array as $val) {
          $return[$val[$key]][] = $val;
      }
      return $return;
  }

  public static function merge($arrIn, $arrIn2) {
    $sum1And2 = [];
    foreach($arrIn as $element) {
      array_push($sum1And2, $element);
    }
    foreach($arrIn2 as $element) {
      array_push($sum1And2, $element);
    }
    return $sum1And2;
  }

}
