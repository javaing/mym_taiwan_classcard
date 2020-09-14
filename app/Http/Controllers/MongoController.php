<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class MongoController extends Controller
{

    public function index()
    {

        DB::connection('mongodb') //选择使用mongodb
            ->collection('users') //选择使用users集合
            ->insert([ //插入数据
                'name' => 'tom',
                'age' => 18
            ]);
    }

    public function allPurchase()
    {
        //$res = DB::connection('mongodb')->collection('Consume')->get(); //查询所有数据
        $res = DB::connection('mongodb')->collection('Purchase')->get(); //查询所有数据
        dd($res); //打印数据
    }
}
