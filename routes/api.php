<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\VoucherController;
use App\Http\Controllers\Seller\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/google-auth', [AuthenticationController::class, 'authGoogle']);
Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/check-otp-register', [AuthenticationController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthenticationController::class, 'resendOtp']);
Route::post('/verify-register', [AuthenticationController::class, 'verifyRegister']);

Route::prefix('/forgot-password')->group(function () {
    Route::post('/request', [ForgotPasswordController::class, 'request']);
    Route::post('/check-otp', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/resend-otp', [ForgotPasswordController::class, 'resendOtp']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
});


Route::post('/login', [AuthenticationController::class, 'login']);
Route::get('/slider', [HomeController::class, 'getSlider']);
Route::get('/category', [HomeController::class, 'getCategory']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'getProfile']);
    Route::patch('profile', [ProfileController::class, 'updateProfile']);

    Route::apiResource('address', AddressController::class);
    Route::post('address/{uuid}/set-default', [AddressController::class, 'setDefault']);

    Route::get('province', [AddressController::class, 'getProvince']);
    Route::get('city', [AddressController::class, 'getCity']);

    Route::get('product', [HomeController::class, 'getProduct']);
    Route::get('product/{slug}', [HomeController::class, 'getProductDetail']);
    Route::get('product/{slug}/review', [HomeController::class, 'getProductReview']);
    Route::get('seller/{username}', [HomeController::class, 'getSellerDetail']);

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'getCart']);
        Route::post('/', [CartController::class, 'addToCart']);
        Route::delete('/{uuid}', [CartController::class, 'removeItemFromCart']);
        Route::patch('/{uuid}', [CartController::class, 'updateItemFromCart']);

        Route::get('/get-voucher', [CartController::class, 'getVoucher']);
        Route::post('/apply-voucher', [CartController::class, 'applyVoucher']);
        Route::post('/remove-voucher', [CartController::class, 'removeVoucher']);

        Route::post('/update-address', [CartController::class, 'updateAddress']);
        Route::get('/shipping', [CartController::class, 'getShipping']);
        Route::post('/shipping-fee', [CartController::class, 'updateShippingFee']);

        Route::post('/checkout', [CartController::class, 'checkout']);
        Route::post('/toggle-coin', [CartController::class, 'toggleCoin']);
    });

    Route::prefix('order')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{uuid}', [OrderController::class, 'show']);
        Route::post('/review/add', [OrderController::class, 'addReview']);
        Route::post('/{uuid}/mark-done', [OrderController::class, 'markAsDone']);
    });

    Route::prefix('seller-dashboard')->group(function () {
        Route::apiResource('product', ProductController::class)->except([
            'show'
        ]);
        Route::apiResource('voucher', VoucherController::class)->except([
            'show'
        ]);
        Route::apiResource('order', SellerOrderController::class)->only([
            'index',
            'show'
        ]);
        Route::post('order/{uuid}/status', [SellerOrderController::class, 'addStatus']);

        Route::get('wallet-transaction', [WalletController::class, 'index']);

        Route::get('list-bank', [WalletController::class, 'getListBank']);

        Route::post('withdraw', [WalletController::class, 'createWithdraw']);
    });
});
