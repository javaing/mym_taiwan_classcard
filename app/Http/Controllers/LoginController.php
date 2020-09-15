<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\LineService;

class LoginController extends Controller
{
    protected $lineService;

    public function __construct(LineService $lineService)
    {
        $this->lineService = $lineService;
    }

    public function pageLine()
    {
        $url = $this->lineService->getLoginBaseUrl();
        Log::info('test 1');
        if (isset($_COOKIE["access_token"])) {
            Log::info(time());
            $url = 'reuse';
        }

        return view('line')->with('url', $url);
    }

    public function saveAccessToken($access)
    {
        //發送一個28天後過期的cookie 
        setcookie('access_token', $access, time() + 3600 * 24 * 28, '/');
        Log::info('save cookie');
        //Log::info($access);
    }

    public function lineLoginCallBack(Request $request)
    {
        Log::info('1');
        try {
            $error = $request->input('error', false);
            if ($error) {
                throw new Exception($request->all());
            }
            Log::info('2');
            $code = $request->input('code', '');

            $response = $this->lineService->getLineToken($code);
            if (array_key_exists('id_token', $response)) {
                //Log::info($response['id_token']);
                $decode = $this->lineService->verifyIDToken($response['id_token']);
                //Log::info($decode);
                //$expires = strtotime('+30 day', time());
                $this->saveAccessToken($response['access_token']);
            }


            $this->askProfile($response['access_token']);
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function askProfile($token)
    {
        // if (!$token) {
        //     $token = config('line.access_token');
        // }
        $user_profile = $this->lineService->getUserProfile($token);
        setcookie('user_profile', $user_profile, time() + 3600 * 24 * 28, '/');
        //echo "<pre>";
        //print_r($user_profile);
        //echo "</pre>";
        app('App\Http\Controllers\ClassCardController')->page();
    }

    public function askProfileReuse()
    {
        $this->askProfile($_COOKIE["access_token"]);
    }
}
