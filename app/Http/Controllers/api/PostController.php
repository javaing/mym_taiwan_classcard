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
        return  DB::collection('UserInfo')->where('UserID', $id)->first();
    }

    private function getValidCard(Request $request)
    {
        $id = $request->userId;
        $dt = Carbon::now();
        Log::info('registerClass dt.' . $dt);
        return DB::collection('Purchase')
            ->where('UserID', $id)
            ->where('Expired', '>', $dt)
            ->where('Points', '>', 0)
            ->first();
    }

    private function getExpiredCard(Request $request)
    {
        $id = $request->userId;
        $dt = Carbon::now();
        Log::info('registerClass dt.' . $dt);
        return DB::collection('Purchase')
            ->where('UserID', $id)
            ->where('Expired', '<', $dt)
            ->where('Points', '>', 0)
            ->first();
    }

    private function getCardId()
    {
        $count = DB::collection('Purchase')->where('CardCreateTime', 'like', '%' . date("Y") . '%')->count() + 1;
        return date("Y") . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function getCard(Request $request)
    {
        $id = $request->cardId;
        $arr = DB::collection('Purchase')->where('CardID', $id);
        return $arr ?  $arr->first() : $arr;
    }

    private function getCardPayment($id)
    {
        $payment = DB::collection('Purchase')->where('CardID', $id)->first()['Payment'];
        //Log::info('getCardPayment=' . json_encode(DB::collection('Purchase')->where('CardID', $id)->first()));
        return  $payment ? $payment : 0;
    }

    private function getCardPoint($id)
    {
        $arr = DB::collection('Purchase')->where('CardID', $id);
        if (!$arr) return 0;
        $point =  $arr->first()['Points'];
        Log::info('getCardPoint=' . $arr->first()['Points']);
        return  $point;
    }

    private function getMongoDateNow()
    {
        $created_at = Carbon::now()->toDateTimeString();
        return new \MongoDB\BSON\UTCDateTime(strtotime($created_at) * 1000);
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
        if (!$this->getUser($request)) {
            DB::collection('UserInfo')
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

    public function showPoint(Request $request)
    {
        $status = 200;
        $content = "success";

        if (!$this->getUser($request)) {
            $content = "無此使用者，請先登入";
        } else {
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
        /*
           "CardID" : "dddd",
    "UserID" : "Jimmy",
    "Points" : 4.0,
    "Payment" : 1800.0,
    "PaymentTime" : ISODate("2020-09-09T14:41:56.779Z"),
    "Expired" : null,
    "CardCreateTime" : null
         */

        $status = 200;
        $content = "success";

        if (!$this->getUser($request)) {
            $content = "無此使用者，請先登入";
            return response($content, $status);
        }

        //是否有舊卡
        $card = $this->getValidCard($request);
        if ($card) {
            $card['message'] = "尚有點數可用";
            $content = $card;
            return response($content, $status);
        }

        $amount = $request->amount;
        $id = $request->userId;
        $point = 4;
        $this->insertPurchase($id, $amount, $point);

        return response($content, $status);
    }

    private function insertPurchase($id, $amount, $point)
    {
        $dt = $this->getMongoDateNow();
        $expired_at = Carbon::now()->add(60, 'day')->toDateTimeString();
        $dt_expired = new \MongoDB\BSON\UTCDateTime(strtotime($expired_at) * 1000);

        $newCard = [
            'CardID' => $this->getCardId(),
            'UserID' => $id,
            'Points' => $point,
            "Expired" => $dt_expired,
            "CardCreateTime" => $dt,
        ];
        if ($amount) {
            $newCard['Payment'] = $amount;
            $newCard['PaymentTime'] = $dt;
        } else {
            $newCard['Payment'] = null;
            $newCard['PaymentTime'] = null;
        }

        Log::info('buyClassCard 5=' . json_encode($newCard));

        DB::collection('Purchase')
            ->insert($newCard);
    }

    public function payUpClassCard(Request $request)
    {
        $status = 200;
        $content = "success";

        $card = $this->getCard($request);
        if (!$card) {
            $content = "無此卡片，請先查明CardID";
            return response($content, $status);
        }

        if (!$request->payment) {
            $content = "儲值金額不得為零";
            return response($content, $status);
        }

        $id = $request->cardId;
        $payment = $this->getCardPayment($id) + $request->payment;
        $dt = $this->getMongoDateNow();
        $newdata = array('$set' => array(
            'Payment' => $payment,
            'PaymentTime' => $dt,
        ));
        DB::collection('Purchase')
            ->where('CardID', $id)
            ->update($newdata, ['upsert' => true]);

        return response($content, $status);
    }

    //register class
    //check if expire
    //add a new record:
    public function registerClass(Request $request)
    {
        $status = 200;
        $content = "success";

        if (!$this->getUser($request)) {
            $content = "無此使用者，請先登入";
            return response($content, $status);
        }

        //是否有舊卡
        $card = $this->getValidCard($request);
        if (!$card) {
            //是否有過期且尚有點數之卡=>可加值
            $card = $this->getExpiredCard($request);
            $card['message'] = "有過期且尚有點數之卡，可補差價續用";
            return response($card, $status);
        }

        $id = $card['CardID'];
        $point = $this->getCardPoint($id) - 1;
        $newdata = array('$set' => array(
            'Points' => $point,
        ));
        DB::collection('Purchase')
            ->where('CardID', $id)
            ->update($newdata, ['upsert' => true]);


        return response($content, $status);
    }

    public function renewClassCard(Request $request)
    {
        $status = 200;
        $content = "success";

        if (!$this->getUser($request)) {
            $content = "無此使用者，請先登入";
            return response($content, $status);
        }

        $card = $this->getExpiredCard($request);
        if (!$card) {
            $content = "無卡可展期";
            return response($content, $status);
        }

        //開新卡
        $amount = 200;
        $userid = $card['UserID'];
        $point = $card['Points'];
        $this->insertPurchase($userid, $amount, $point);

        //舊卡歸零
        $newdata = array('$set' => array(
            'Points' => 0,
        ));
        DB::collection('Purchase')
            ->where('CardID', $card['CardID'])
            ->update($newdata, ['upsert' => true]);

        return response($content, $status);
    }



    public function upload(Request $request)
    {
        $target_dir = "uploads/";
        $target_file_name = $target_dir . basename($_FILES["file"]["name"]);
        $response = array();

        // Check if image file is an actual image or fake image  
        if (isset($_FILES["file"])) {
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file_name)) {
                $success = true;
                $message = "Successfully Uploaded";
            } else {
                $success = false;
                $message = "Error while uploading";
            }
        } else {
            $success = false;
            $message = "Required Field Missing";
        }
        $response["success"] = $success;
        $response["message"] = $message;
        echo json_encode($response);
    }
}
