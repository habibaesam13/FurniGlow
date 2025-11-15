<?php

use App\Models\Category;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\OTPLogicController;
use App\Http\Middleware\EnsureSingleSession;
use App\Http\Controllers\FavouriteController;

Route::get('/', function () {
    return view('Auth.login');
});
Route::get("/login", function () {
    return view("Auth.login");
})->name("login");
Route::post('/login', [AuthController::class, 'login'])->name("auth.login");

Route::get("/register", function () {
    return view("Auth.signup");
});
Route::post('/register', [AuthController::class, 'register'])->name("auth.register");

/**             Password Reset Routes                   */
Route::get('/forgetPassword', [OTPLogicController::class, 'forgotPassword'])->name('password.forget');
Route::post("/sendOtp", [OTPLogicController::class, 'sendOtp'])->name('sendOtp');
Route::get('/reset-password', [OTPLogicController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [OTPLogicController::class, 'resetPassword'])->name('password.reset');


/**                   Authenticated Routes                          */
//user Routes
Route::middleware(['auth', 'single.session'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name("logout");

    Route::get("/home-page", [HomePageController::class, 'index'])->name('home');

    Route::get('/category-products/{categoryId}', [ProductController::class, 'show'])->name('category-products');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/product-details/{id}', [ProductController::class, 'details'])->name('product-details');


    Route::get('/favourites', [FavouriteController::class, 'index'])->name('favourites.index');
    Route::post('/favourites/toggle', [FavouriteController::class, 'toggle'])->name('favourites.toggle');

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{productId}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{productId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{productId}', [CartController::class, 'delete'])->name('cart.delete');


    //order routes
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Checkout page for stripe payment 
    Route::get('/checkout/success', function () {
        return view('checkout.success');
    })->name('checkout.success');
    Route::post('/checkout/confirm-order/{order}', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
    Route::get('/checkout/{order}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/stripe/webhook', [WebhookController::class, 'handle'])->name('stripe.webhook');
});

//Admin Routes
Route::middleware(['auth', 'single.session'])->prefix('/admin')->group(function () {

    Route::get('/dashboard', function () {
        return view('admin.AdminDashboard');
    })->name("AdminDashboard");
});
