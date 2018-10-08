<?php

use Illuminate\Http\Request;
use App\Models\cacheFeedDData;

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

Route::any('add-feed', 'feedsApiController@addFeed');
Route::any('merge-feeds', 'feedsApiController@mergeFeeds');
Route::any('list-feeds', 'feedsApiController@listFeeds');
