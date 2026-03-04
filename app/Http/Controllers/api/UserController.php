<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 回傳當前已驗證使用者（供 route:cache 序列化用，不可用 Closure）
     */
    public function __invoke(Request $request)
    {
        return $request->user();
    }
}
