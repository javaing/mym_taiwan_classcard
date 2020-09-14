<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class PostController extends Controller
{
    public function update($postId)
    {
        print_r("update輸入{$postId}");
    }


    private function getUser(Request $request)
    {
        $id = $request->userId;
        return  DB::connection('mongodb')
            ->collection('UserInfo')->where('UserID', $id)->first();
    }

    private function getValidCard(Request $request)
    {
        $id = $request->userId;
        $dt = Carbon::now();
        Log::info('registerClass dt.' . $dt);
        $card =  DB::connection('mongodb')->collection('Purchase')
            ->where('UserID', $id)
            ->where('Expired', '>', $dt)
            ->first();
    }

    public function createUser(Request $request)
    {
        /* Line:
        ([userId] => Ub3b2f4478935abb4d54969109bac6d23
    [displayName] => art伯勳
    [pictureUrl] => https://profile.line-scdn.net/0hoyldZOXtMFZRHRjzyAdPAW1YPjsmMzYeKXorMnEUb2V9eiAJOn98MXcUamN4KCACbCh-NCYdOWZ8
    [statusMessage] => 白露)
        */
        /* Mongo UserInfo:
        {
    "_id" : ObjectId("5f57acfbce956b88bb99f568"),
    "UserID" : "Jimmy",
    "NickName" : "Jimmy",
    "UserName" : "Jimmy Liao",
    "Phone" : "0800080168",
    "Mobile" : "0800080168",
    "Address" : "MYM, TW",
    "Referrer" : "God"}
        */

        $id = $request->userId;


        $status = 200;
        //print_r("userid=" . $id);
        if (!$this->getUser($request)) {
            DB::connection('mongodb')
                ->collection('UserInfo')
                ->insert([
                    'UserID' => $id,
                    "NickName" => $request->displayName,
                    "Email" => $request->email,
                    "PictureUrl" => $request->pictureUrl,
                ]);
            $content = "success";
        } else {
            $content = "user already exist";
        }

        return response($content, $status);
    }



    //register class
    //check if expire
    //add a new record:
    public function showPoint(Request $request)
    {
        $status = 200;
        $content = "success";

        $id = $request->userId;

        if (!$this->getUser($request)) {
            $content = "無此使用者，請先登入";
        } else {
            $dt = Carbon::now();
            Log::info('registerClass dt.' . $dt);
            $card = $this->getValidCard($request);
            if (!$card) {
                $content = "購買課卡由此 xxxx";
            } else {
                $content = $card;
            }
        }

        return response($content, $status);
    }

    public function buyClassCard(Request $request)
    {
        $status = 200;
        $content = "success";

        $amount = $request->amount;
        $id = $request->userId;

        if (!$this->getUser($request)) {
            $content = "無此使用者，請先登入";
        } else {
            if (!$amount) $amount = 1800;
            DB::connection('mongodb')
                ->collection('Purchase')
                ->insert([
                    'UserID' => $id,
                    "NickName" => $request->displayName,
                    "Email" => $request->email,
                    "PictureUrl" => $request->pictureUrl,
                ]);
        }

        return response($content, $status);
    }
}
