<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class LineService
{

    public function registerClassUrl($user, $index)
    {
        $url = 'registeclass/' . $index;
        return $url;
    }

    public function getLoginBaseUrl()
    {
        // 組成 Line Login Url
        $url = config('line.authorize_base_url') . '?';
        $url .= 'response_type=code';
        $url .= '&client_id=' . config('line.channel_id');
        $url .= '&redirect_uri=' . config('app.url') . '/callback/login';
        $url .= '&state=test'; // 暫時固定方便測試
        $url .= '&scope=openid%20profile%20email'; //眉角在這裡，scope要加email

        return $url;
    }

    public function getLineToken($code)
    {

        $client = new Client();
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $response = $client->request('POST', config('line.get_token_url'), [
            //'debug' => true,
            'headers' => $headers,
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => config('line.channel_id'),
                'client_secret' => config('line.secret'),
                'code' => $code,
                'redirect_uri' => config('app.url') . '/callback/login',
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getUserProfile($token)
    {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $response = $client->request('GET', config('line.get_user_profile_url'), [
            'headers' => $headers
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }


    public function verifyIDToken($token)
    {
        $client = new Client();
        $response = $client->request('POST', config('line.verify_token_url'), [
            'form_params' => [
                'id_token' => $token,
                'client_id' => config('line.channel_id'),
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }
}
