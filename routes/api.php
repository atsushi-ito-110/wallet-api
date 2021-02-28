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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => ['api']], function(){
    Route::resource('credits', 'App\Http\Controllers\Api\CreditsController', ['only' => ['index']]);
    Route::resource('categories', 'App\Http\Controllers\Api\CategoriesController', ['only' => ['index']]);
    Route::post('categories/import', [
        'as' => 'categories.import','uses' => 'App\Http\Controllers\Api\CategoriesController@import',
    ]);
});
