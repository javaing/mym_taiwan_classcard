<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;


class Tools
{

  public static function getBuyCardPassword() {
    srand(date("ymd"));
    return substr( strval(rand()),-4);
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
