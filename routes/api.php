<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FollowingController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('v1')->group(function(){
    Route::apiResource('posts', PostController::class)->middleware('auth:sanctum');
    Route::controller(AuthController::class)->group(function(){
        Route::post('/register', 'register')->name('register');
        Route::post('/login', 'login')->name('login');

        Route::post('/logout', 'logout')->name('logout')->middleware('auth:sanctum');
    });


    Route::controller(FollowingController::class)->group(function(){
        Route::middleware('auth:sanctum')->group(function(){
            Route::prefix('users')->group(function(){
                Route::post('{username}/follow', 'follow')->name('users.follow');
                Route::delete('{username}/unfollow', 'unfollow')->name('users.unfollow');
            });
        });
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
