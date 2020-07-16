<?php

// use Illuminate\Http\Request;
// use Illuminate\Routing\Route;

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

// use App\Category;
// use App\Http\Controllers\CategoryController;

Route::prefix('v1')->group(function(){
    Route::middleware('cors')->group(function(){
        Route::post('login', 'AuthController@login');
        Route::post('register','AuthController@register');
    
        Route::get('categories/random','CategoryController@random');
        Route::get('books/top','BookController@top');
        Route::get('categories','CategoryController@index');
        Route::get('category/slug/{slug}','CategoryController@slug');
        Route::get('books','BookController@index');
        Route::get('book/slug/{slug}','BookController@slug');
        Route::get('books/search/{keyword}','BookController@search');

        Route::get('provinces','ShopController@provinces');
        Route::get('cities','ShopController@cities');
        Route::get('couriers','ShopController@couriers');
            
        Route::middleware('auth:api')->group(function(){
            Route::get('send-notif','NotifikasiController@sendNotif');
            Route::post('info/user', 'AuthController@infoUser');
            Route::post('logout','AuthController@logout');
            Route::post('shipping','ShopController@shipping');
            Route::post('service','ShopController@service');
            Route::post('payment','ShopController@payment');
            Route::post('myorder','ShopController@myOrder');
        });
    });
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
