<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', 'LoginController@pageLine');
//Route::get('/class', 'ClassCardController@page');
//Route::get('/registeclass/{index}', 'ClassCardController@registeclass')->where('index', '[0-9]+');
Route::get('/line', 'LoginController@pageLine');
Route::get('/callback/login', 'LoginController@lineLoginCallBack');
Route::get('line/reuse/', 'LoginController@askProfileReuse')->name('reuse.line');
Route::get('/alluser/{userId?}', 'LoginController@alluser')->middleware('auth.basic');
Route::post('/alluser', 'LoginController@updateUser')->middleware('auth.basic');
Route::get('/logout', 'LoginController@logout');

//買卡
Route::get('classcard/buy', 'ClassCardController@buyClassCard')->name('buy.classcard');
Route::post('classcard/buy', 'ClassCardController@buyClassCardPost')->name('buy.classcardPost');
Route::get('buyNewCard', 'ClassCardController@buyNewCardView')->name('buy.newcard');
Route::get('classcard/buycardpass', 'ClassCardController@buyCardPass');

//上課紀錄
Route::get('classcard/history', 'ClassCardController@showClassHistoryByCookie');
Route::get('classcard/history/{userId}/{index}', 'ClassCardController@showClassHistory')->name('show.classhistory');
//顯示課卡
Route::get('classcard/show/{cardId}', 'ClassCardController@showClassCard')->where('cardId', '[0-9A-Za-z=]+');
//蓋課卡
Route::get('/registe/{point}/{cardId}', 'ClassCardController@registeclassByPoint')
    ->where(['point' => '[0-9]+', 'cardId' => '[0-9A-Za-z=]+'])->name('registe.classcard');
//補繳
Route::get('classcard/extend', 'ClassCardController@extendCard')->name('extend.classcard');
//手動蓋課卡
Route::get('/classcard/byhand', 'AccountController@classByhand');
Route::Post('/classcard/byhand', 'AccountController@registeclassByhand');
//後台
Route::get('/account/balance', 'AccountController@create');
Route::Post('/account/balance', 'AccountController@balance');
//退款
Route::get('account/deposite', 'AccountController@deposite')->name('account.deposite');
Route::get('/account/carddetail/{cardId}', 'AccountController@cardDetail')->where('cardId', '[0-9A-Za-z=]+')->name('account.cardDetail');
Route::get('/balance/byuser/{userId}', 'AccountController@balanceByUser')->where('userId', '[0-9A-Za-z=]+');

Route::get('/account/balance2', 'Balance2Controller@balance2');
Route::post('/account/balance2', 'Balance2Controller@balance2post');
Route::post('/account/balance2', 'Balance2Controller@balance2FreeRange');
Route::post('/account/carddetail2', 'Balance2Controller@cardDetail2');
Route::post('/download', 'Balance2Controller@downloadFile');
Route::post('/downloadByName', 'Balance2Controller@downloadFileGroupByname');
Route::post('/downloadByKind', 'Balance2Controller@downloadFileGroupByKind');

//購買線上課程
Route::get('/onlineclass/buy', 'OnlineClassController@list');
Route::post('/onlineclass/buy', 'OnlineClassController@buy');
//線上課程明細(含退款功能)
Route::get('/onlineclass/detail', 'OnlineClassController@cardDetail')->name('onlineclass.cardDetail');
Route::get('/onlineclass/refund', 'OnlineClassController@refund')->name('onlineclass.refund');
//蓋課卡(人工扣點)
Route::get('/onlineclass/byhand', 'OnlineClassController@listByhand');
Route::Post('/onlineclass/byhand', 'OnlineClassController@registeByhand');
//線上課程使用紀錄(供學員查詢)
Route::get('/onlineclass/history', 'OnlineClassUserController@history');
Route::get('/onlineclass/history/{userId}/{index}', 'OnlineClassUserController@historyPick')->name('onlineclass.historyPick');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
