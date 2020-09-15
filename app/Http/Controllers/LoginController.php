<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\LineService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        if (!array_key_exists('email', $user_profile)) {
            $user_profile['email'] = '';
        }
        //setcookie('user_profile', $user_profile, time() + 3600 * 24 * 28, '/');
        //echo "<pre>";
        //print_r($user_profile);
        //echo "</pre>";
        $this->page($user_profile);
    }

    public function askProfileReuse()
    {
        $this->askProfile($_COOKIE["access_token"]);
    }


    private function getValidCard($id)
    {
        //$id = $request->userId;
        $dt = Carbon::now();
        return DB::collection('Purchase')
            ->where('UserID', $id)
            ->where('Expired', '>', $dt)
            ->where('Points', '>', 0)
            ->first();
    }

    private function getUser($id)
    {
        //$id = $request->userId;
        return  DB::collection('UserInfo')->where('UserID', $id)->first();
    }

    public function page($user_profile)
    {
        $status = 200;
        $content = "success";

        //讀取該user狀態 from API
        //買新卡 call API
        //仍有剩餘格數 蓋過秀灰色，不可按
        $id = $user_profile['userId'];
        if (!$this->getUser($id)) {
            DB::collection('UserInfo')
                ->insert([
                    'UserID' => $id,
                    "NickName" => $user_profile['displayName'],
                    "Email" => $user_profile['email'],
                    "PictureUrl" => $user_profile['pictureUrl'],
                ]);
            Log::info('pag()=insert UserInfo');
        }
        $card = $this->getValidCard($id);
        Log::info('pag()=getValidCard =' . json_encode($card));
        if (!$card) {
            return view('buynewcard');
        }
        $index = $card['Points'];
        $url = $this->lineService->registerClassUrl($user_profile['displayName'], $index);
        return view('classcard', [
            'url' => $url,
            'used' => $index
        ]);
    }

    public function buyClassCard($id)
    {
        $index = 0;
        $url = $this->lineService->registerClassUrl($id, $index);
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
