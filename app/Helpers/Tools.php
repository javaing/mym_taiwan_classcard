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

}
