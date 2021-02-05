<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;


class Tools
{

  public static function getBuyCardPassword() {
    srand(date("ymd"));
    return substr( strval(rand()),-4);
  }


}
