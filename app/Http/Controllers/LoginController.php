<?php

namespace App\Http\Controllers;

use App\Helpers\DBHelper as HelpersDBHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\LineService;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    protected $lineService;
    protected $ONLINECLASS = 'onlineclass';
    protected $TAICHUNG = 'taichung';

    public function __construct(LineService $lineService)
    {
        $this->lineService = $lineService;
    }

    /** 部分主機／健康檢查會打 /index.php，導向首頁（供 route:cache 序列化用） */
    public function redirectToRoot()
    {
        return redirect('/');
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

        if ($this->shouldShowLocationHome()) {
            $taipeiUrl = $url === 'reuse' ? route('reuse.line') : $url;

            return view('line-location', [
                'taipeiUrl' => $taipeiUrl,
                'taichungUrl' => route('taichung.login'),
            ]);
        }

        return view('line')->with('url', $url);
    }

    /**
     * 測試期間於台灣時間週二、週六以外的 07:00～11:59 顯示新首頁。
     */
    private function shouldShowLocationHome()
    {
        $now = Carbon::now('Asia/Taipei');

        return !in_array($now->dayOfWeekIso, [2, 6], true)
            && $now->hour >= 7
            && $now->hour < 12;
    }
    //onlineclassLogin
    public function onlineclassLogin()
    {
        $url = $this->lineService->getLoginBaseUrlBy($this->ONLINECLASS);
        if (isset($_COOKIE["access_token"])) {
            $url = 'reuse';
        }
        Log::info('pageLine()=' . $url);

        return view('line')->with('url', $url);
    }

    public function taichungLogin(Request $request)
    {
        $accessToken = $_COOKIE['access_token'] ?? null;
        if ($accessToken) {
            try {
                return $this->askProfile($accessToken, $this->TAICHUNG);
            } catch (ClientException $exception) {
                Log::warning('Taichung LINE token expired; restart login');
                setcookie('access_token', '', $this->cookieOptions(time() - 3600));
                $request->session()->forget([
                    'line_user_id',
                    'taichung_authenticated',
                    'taichung_activity_authorization',
                ]);
            }
        }

        $state = $this->TAICHUNG . ':' . Str::random(40);
        $request->session()->put('line_oauth_state', $state);

        return redirect($this->lineService->getLoginBaseUrlBy($state));
    }

    public function logout(Request $request)
    {
        $accessToken = $_COOKIE['access_token'] ?? null;

        $request->session()->forget([
            'line_user_id',
            'line_oauth_state',
            'taichung_authenticated',
            'taichung_activity_authorization',
        ]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        setcookie('access_token', '', $this->cookieOptions(time() - 3600));
        setcookie('userId', '', $this->cookieOptions(time() - 3600));

        if ($accessToken) {
            Log::info('logout LINE access token');
            return $this->lineService->logout($accessToken);
        }
        return "無access_token無法logout";
    }

    public function saveAccessToken($access)
    {
        //發送一個28天後過期的cookie
        setcookie('access_token', $access, $this->cookieOptions(time() + 3600 * 24 * 28));
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
            $state = $request->input('state', '');

            if ($state === $this->TAICHUNG) {
                return response('無效的 LINE 登入驗證，請重新登入。', 419);
            }

            if (Str::startsWith($state, $this->TAICHUNG . ':')) {
                $expectedState = $request->session()->pull('line_oauth_state');
                if (!$expectedState || !hash_equals($expectedState, $state)) {
                    return response('LINE 登入驗證已過期，請重新登入。', 419);
                }
                $state = $this->TAICHUNG;
            }

            $response = $this->lineService->getLineToken($code);
            if (array_key_exists('id_token', $response)) {
                $this->saveAccessToken($response['access_token']);
            }

            return $this->askProfile($response['access_token'], $state);
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function askProfile($token, $state)
    {
        $user_profile = $this->lineService->getUserProfile($token);
        if (!array_key_exists('email', $user_profile)) {
            $user_profile['email'] = '';
        }
        $this->saveUserInfo($user_profile);

        if($state==$this->ONLINECLASS) {
          return redirect('/onlineclass/history');
        }
        if ($state == $this->TAICHUNG) {
            request()->session()->forget('taichung_activity_authorization');
            request()->session()->regenerate();
            session([
                'line_user_id' => $user_profile['userId'],
                'taichung_authenticated' => true,
            ]);
            return redirect()->route('taichung.activities');
        }
        return $this->showPoints($user_profile['userId']);
    }

    public function askProfileReuse()
    {
        if (strpos(config('app.url', ''), '127.0.0.1') !== false) {
            $user_profile = [
                "userId" => "Ub3b2f4478935abb4d54969109bac6d23",
                "displayName" => "art伯勳",
                "pictureUrl" => "https://profile.line-scdn.net/0hoyldZOXtMFZRHRjzyAdPAW1YPjsmMzYeKXorMnEUb2V9eiAJOn98MXcUamN4KCACbCh-NCYdOWZ8",
                "statusMessage" => "白露"
            ];
            return $this->showPoints($user_profile['userId']);
        }
        return $this->askProfile($_COOKIE["access_token"], ''); //空字串表示走預設
    }

    public function saveUserInfo($user_profile) {
      $userId = $user_profile['userId'];
      if (!HelpersDBHelper::getUser($userId)) {
          HelpersDBHelper::insertNewUser($user_profile);
      }
      setcookie('userId', $userId, $this->cookieOptions(time() + 3600 * 24 * 28));
    }

    private function cookieOptions($expires)
    {
        return [
            'expires' => $expires,
            'path' => '/',
            'secure' => Str::startsWith(config('app.url', ''), 'https://'),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }


    public function showPoints($userId)
    {
        //讀取該user狀態 from API
        //買新卡 call API
        //仍有剩餘格數 蓋過秀灰色，不可按
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

    public function checkField($key, $lookfor)
    {
        if (!array_key_exists($key, $lookfor)) $lookfor[$key] = '';
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
                'Location' => $request->Location,
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
