<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('{postId}/update', 'api\PostController@update')->where('postId', '[0-9]+');
Route::post('/createUser', 'api\PostController@createUser');
Route::post('/showPoint', 'api\PostController@showPoint');
Route::post('/buyClassCard', 'api\PostController@buyClassCard');
Route::post('/payUpClassCard', 'api\PostController@payUpClassCard');
Route::post('/registerClass', 'api\PostController@registerClass');
Route::post('/renewClassCard', 'api\PostController@renewClassCard');
