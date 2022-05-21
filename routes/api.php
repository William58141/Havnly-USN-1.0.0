<?php

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

Route::get('/help', [App\Http\Controllers\Api\HelpController::class, 'index'])->name('help.index');

Route::post('/token', [App\Http\Controllers\Api\AuthController::class, 'index'])->name('token.index');
Route::put('/token', [App\Http\Controllers\Api\AuthController::class, 'update'])->name('token.update');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/banks', [App\Http\Controllers\Api\BankController::class, 'index'])->name('banks.index');
    Route::get('/banks/{id}', [App\Http\Controllers\Api\BankController::class, 'show'])->name('banks.show');

    Route::get('/accounts', [App\Http\Controllers\Api\AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/{id}', [App\Http\Controllers\Api\AccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{id}/balances', [App\Http\Controllers\Api\AccountController::class, 'showBalances'])->name('accounts.showBalances');
    Route::get('/accounts/{id}/transactions', [App\Http\Controllers\Api\AccountController::class, 'showTransactions'])->name('accounts.showTransactions');

    Route::post('/payments', [App\Http\Controllers\Api\PaymentController::class, 'newPayment'])->name('payments.newPayment');

    Route::get('/resources/{id}', [App\Http\Controllers\Api\ResourceController::class, 'show'])->name('resources.show');
});
