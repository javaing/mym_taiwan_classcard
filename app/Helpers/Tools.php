<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;


class Tools
{

  /**
   * 產生「當日購卡密碼」（固定4位數字），以固定密碼字串加上日期做 crc32 雜湊後取餘數湊成4位數。
   * 改用 crc32 而非 srand()+rand()，避免依賴 PHP/OS 底層 rand 實作差異，
   * 確保同一天在任何時間、任何伺服器上都能得到一致的結果，同時維持過去純數字密碼的習慣。
   */
  public static function getBuyCardPassword() {
    $secret = config('line.buy_newcard_pass', 'bhakti');
    // sprintf('%u', ...) 確保在32-bit PHP環境下也能拿到正確的unsigned值，避免crc32()回傳負數
    $hash = sprintf('%u', crc32($secret . date('Ymd')));
    return str_pad((string) ($hash % 10000), 4, '0', STR_PAD_LEFT);
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
