<?php

use App\Http\Controllers\FoodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
  Route::get('user', [UserController::class, 'fetch']);
  Route::post('user', [UserController::class, 'updateProfile']);
  Route::post('user/photo', [UserController::class, 'updatePhoto']);
  Route::post('logout', [UserController::class, 'logout']);

  // Route::post('checkout', [TransactionController::class, 'checkout']);
  // Route::get('transaction', [TransactionController::class, 'all']);
  // Route::post('transaction/{id}', [TransactionController::class, 'update']);
});

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::get('food', [FoodController::class], 'all');
