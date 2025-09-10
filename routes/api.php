<?php

use App\Http\Controllers\Transaction\StoreTransactionController;
use App\Http\Controllers\Wallet\StoreWalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/wallets', StoreWalletController::class)->name('wallets.store');

Route::post('/transfer', StoreTransactionController::class)->name('transactions.store');
