<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ReferralsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::post('/logout', [UserAuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);
    return ['token' => $token->plainTextToken];
});

// list products
Route::get('/products', [ProductsController::class, 'list'])->middleware('auth:sanctum');
Route::post('/products/process-transaction', [ProductsController::class, 'processTransaction'])->middleware('auth:sanctum');

// dashboard

Route::get('/dashboard', [DashboardController::class, 'init'])->middleware('auth:sanctum');

// transactions
Route::get('/transactions/{status}', [TransactionsController::class, 'list'])->middleware('auth:sanctum');

// coupons
Route::post('/coupons/new', [CouponsController::class, 'new'])->middleware('auth:sanctum');
Route::get('/coupons/list/{status}', [CouponsController::class, 'list'])->middleware('auth:sanctum');
Route::get('/coupons/redeemed', [CouponsController::class, 'redeemed'])->middleware('auth:sanctum');
Route::post('/coupons/redeem', [CouponsController::class, 'redeem'])->middleware('auth:sanctum');

// referrals
Route::get('/referrals', [ReferralsController::class, 'list'])->middleware('auth:sanctum');

// logs
Route::get('/logs/logins', [ProfileController::class, 'getLogLogins'])->middleware('auth:sanctum');

Route::group(['prefix'=>'admin','as'=>'admin.', 'middleware' => 'auth:sanctum'], function(){
    // clients
    Route::get('/clients', [ClientsController::class, 'list']);
    Route::get('/clients/roles', [ClientsController::class, 'getRoles']);
    Route::get('/clients/{id}', [ClientsController::class, 'getUserDetails']);

    // deposits
    Route::get('/deposits', [DepositController::class, 'list']);
});

Route::get("/test", [ApiController::class, 'test']);
