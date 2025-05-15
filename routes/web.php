<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/testing-email', function () {
    $order = \App\Models\Order\Order::orderBy('id', 'desc')->first();

    return new \App\Mail\NewOrderToSeller($order);
});
Route::post('/midtrans/callback', [App\Http\Controllers\MidtransController::class, 'callback']);
