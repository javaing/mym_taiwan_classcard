<?php

namespace App\Http\Controllers;

use App\Helpers\DBHelper as HelpersDBHelper;
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
                //$decode = $this->lineService->verifyIDToken($response['id_token']);
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
        $user_profile = $this->lineService->getUserProfile($token);
        if (!array_key_exists('email', $user_profile)) {
            $user_profile['email'] = '';
        }
        return $this->showPoints($user_profile);
    }

    public function askProfileReuse()
    {
        if (strpos(env('APP_URL'), '127.0.0.1')) {
            $user_profile = [
                "userId" => "Ub3b2f4478935abb4d54969109bac6d23",
                "displayName" => "art伯勳",
                "pictureUrl" => "https://profile.line-scdn.net/0hoyldZOXtMFZRHRjzyAdPAW1YPjsmMzYeKXorMnEUb2V9eiAJOn98MXcUamN4KCACbCh-NCYdOWZ8",
                "statusMessage" => "白露"
            ];
            return $this->showPoints($user_profile);
        } else {
            return $this->askProfile($_COOKIE["access_token"]);
        }
    }


    public function showPoints($user_profile)
    {
        //讀取該user狀態 from API
        //買新卡 call API
        //仍有剩餘格數 蓋過秀灰色，不可按
        $userId = $user_profile['userId'];
        if (!HelpersDBHelper::getUser($userId)) {
            HelpersDBHelper::insertNewUser($user_profile);
        }
        $card = HelpersDBHelper::getValidCard($userId);
        if (!$card) {
            return view("buynewcard")->with('userId', $userId);
            //print_r($user_profile);
            //return;
        }
        $point = $card['Points'];
        //$url = $this->lineService->registerClassUrl($user_profile['displayName'], $point);

        Log::info('showPoints()= userid=' . $userId . ' cardId=' . $card['CardID'] . ' point=' . $point);
        //Log::info('showPoints card=' . json_encode($card));
        return view('classcard', [
            'userId' => $userId,
            'cardId' => $card['CardID'],
            'point' => $point,
        ]);
    }
}
