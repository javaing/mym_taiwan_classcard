<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ClassCardService
{
    public function getLoginBaseUrl($user, $index)
    {
        $url = 'registeclass/' . $index;
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
            //'headers' => $headers,
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
