<?php

use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\API\FoodController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

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

Route::post('login', [UserController::class, 'login']);
Route::get('unauthorized', function () {
    // return unauthorized message
    return ResponseFormatter::error([
        'message' => 'Unauthorized'
    ], 'Unauthorized', 401);
})->name('api.unauthorized');

Route::post('register', [UserController::class, 'register']);
Route::get('register', function () {
    return ResponseFormatter::error(
        ['message' => 'Method Get is Forbidden'],
        'Don\'t come again',
        418
    );
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('user/photo', [UserController::class, 'updatePhoto']);
    Route::post('logout', [UserController::class, 'logout']);
});

Route::get('food', [FoodController::class, 'all']);
