<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/class', 'ClassCardController@page');
Route::get('/registeclass/{index}', 'ClassCardController@registeclass')->where('index', '[0-9]+');
Route::get('/line', 'LoginController@pageLine');
Route::get('/callback/login', 'LoginController@lineLoginCallBack');
Route::get('line/reuse/', 'LoginController@askProfileReuse')->name('reuse.line');
Route::get('classcard/buy/', 'LoginController@buyClassCard')->name('buy.classcard');
Route::get('/registe/{point}/{cardId}', 'ClassCardController@registeclassByPoint')
    ->where(['point' => '[0-9]+', 'cardId' => '[0-9]+'])->name('registe.classcard');
