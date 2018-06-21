<?php

use Illuminate\Http\Request;
use App\Article;
use App\Auth\Register;

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
Route::get('articles','ArticleController@index');
Route::get('articles/{article}','ArticleController@show');
Route::post('articles','ArticleController@store');
Route::post('register', 'Auth\RegisterController@register');