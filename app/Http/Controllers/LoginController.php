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

    //如換domain name，請更新.env APP_URL
    public function pageLine()
    {
        $url = $this->lineService->getLoginBaseUrl();
        if (isset($_COOKIE["access_token"])) {
            //Log::info(time());
            $url = 'reuse';
        }
        Log::info('pageLine()=' . $url);

        return view('line')->with('url', $url);
    }


    public function logout()
    {
        if (isset($_COOKIE["access_token"])) {
            Log::info($_COOKIE["access_token"]);
            return $this->lineService->logout($_COOKIE["access_token"]);
        }
        return "無access_token無法logout";
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
        try {
            $error = $request->input('error', false);
            if ($error) {
                throw new Exception($request->all());
            }
            $code = $request->input('code', '');

            $response = $this->lineService->getLineToken($code);
            if (array_key_exists('id_token', $response)) {
                $this->saveAccessToken($response['access_token']);
            }


            return $this->askProfile($response['access_token']);
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
        }
        return $this->askProfile($_COOKIE["access_token"]);
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
        setcookie('userId', $userId, time() + 3600 * 24 * 28, '/');
        $card = HelpersDBHelper::getValidCardNoMatter($userId);
        if (!$card) {
            return view("buynewcard")->with('userId', $userId);
        }

        return redirect('classcard/show/' . base64_encode($card['CardID']));
    }

    public function alluser(Request $request, $arg1 = null)
    {
        $users = HelpersDBHelper::getUsers();
        $detail = null;
        if ($arg1) {
            $detail = HelpersDBHelper::getUser($arg1);

            $check = array('UserName', 'Mobile', 'Address', 'Referrer', 'Email', 'PersonalID');
            foreach ($check as $key) {
                $this->checkField($key, $detail);
            }
        }
        //Log::info('detail=' . implode("|", $detail));
        return view("alluser")->with(['users' => $users, 'userDetail' => $detail]);
    }

    public function checkField($key, $search)
    {
        if (!array_key_exists($key, $search)) $search[$key] = '';
    }


    public function updateUser(Request $request)
    {
        $userDetail = null;
        $uid = $request->UserID;
        if ($uid) {
            //update userinfo
            $datas = array(
                'NickName' => $request->NickName,
                'UserName' => $request->UserName,
                'Mobile' => $request->Mobile,
                'Address' => $request->Address,
                'Referrer' => $request->Referrer,
                'Email' => $request->Email,
                'PersonalID' => $request->PersonalID,
            );
            HelpersDBHelper::updateUser($uid, $datas);

            $userDetail = HelpersDBHelper::getUser($uid);
        } else {
            Log::info('No userId to update!');
        }


        $users = HelpersDBHelper::getUsers();
        //Log::info('userDetail=' . $userDetail);
        return view("alluser")->with(['users' => $users, 'userDetail' => $userDetail]);
    }
}
