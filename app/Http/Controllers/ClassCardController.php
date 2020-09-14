<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ClassCardService;

class ClassCardController extends Controller
{
    protected $classcardService;

    public function __construct(ClassCardService $classcardService)
    {
        $this->classcardService = $classcardService;
    }

    public function page($index = 0)
    {
        //讀取該user狀態 from API
        //買新卡 call API
        //仍有剩餘格數 蓋過秀灰色，不可按
        $url = $this->classcardService->getLoginBaseUrl('test_user', $index);
        return view('classcard', [
            'url' => $url,
            'used' => $index
        ]);
    }

    public function registeclass($index)
    {
        return $this->page($index + 1);
    }
}