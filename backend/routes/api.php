<?php

use App\Http\Controllers\BalanceController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/balance/{user}', [BalanceController::class, 'show'])->name('balance.show');
Route::post('/deposit', [TransactionController::class, 'deposit'])->name('transaction.deposit');
Route::post('/withdraw', [TransactionController::class, 'withdraw'])->name('transaction.withdraw');
Route::post('/transfer', [TransactionController::class, 'transfer'])->name('transaction.transfer');
