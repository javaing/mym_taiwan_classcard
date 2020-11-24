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

Route::get('classcard/{cardId}', 'ClassCardController@showClassCard')->where('cardId', '[0-9]+');
Route::get('classcard/buy/', 'ClassCardController@buyClassCard')->name('buy.classcard');
Route::get('classcard/history/{userId}/{index}', 'ClassCardController@showClassHistory')->name('show.classhistory');
Route::get('/registe/{point}/{cardId}', 'ClassCardController@registeclassByPoint')
    ->where(['point' => '[0-9]+', 'cardId' => '[0-9]+'])->name('registe.classcard');
Route::get('classcard/history', 'ClassCardController@showClassHistoryByCookie');

Route::get('/classcard/byhand', 'AccountController@classByhand');
Route::Post('/classcard/byhand', 'AccountController@registeclassByhand');
Route::get('/account/balance', 'AccountController@create');
Route::Post('/account/balance', 'AccountController@balance');
Route::get('/account/{cardId}', 'AccountController@cardDetail')->where('cardId', '[0-9]+')->name('account.cardDetail');
Route::get('account/deposite', 'AccountController@deposite')->name('account.deposite');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
