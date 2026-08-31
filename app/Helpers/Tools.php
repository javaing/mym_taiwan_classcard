<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;


class Tools
{

  /**
   * 產生「當日購卡密碼」（固定4位數字）。
   * 以不進版本庫的 LINE_SECRET 與日期做 HMAC，避免密碼可由公開程式碼推算；
   * 只取7個十六進位字元，確保32-bit PHP也能安全轉為整數。
   */
  public static function getBuyCardPassword() {
    $hash = hash_hmac('sha256', date('Ymd'), (string) config('line.secret'));
    $number = hexdec(substr($hash, 0, 7)) % 10000;
    return str_pad((string) $number, 4, '0', STR_PAD_LEFT);
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
