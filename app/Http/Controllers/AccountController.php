<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ClassCardService;
use App\Helpers\DBHelper as DBHelper;
use Carbon\Carbon;

class AccountController extends Controller
{
    //protected $classcardService;

    public function __construct()
    {
        //$this->classcardService = $classcardService;
    }


    public function balance()
    {

        return view('balance', [
            'arr' => null,
        ]);
    }
}
