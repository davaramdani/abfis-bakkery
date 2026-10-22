<?php

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('user.dashboard');
})->name('home');

Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
Route::get('/products/{id}', [UserDashboardController::class, 'show'])->name('products.show');
Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');


Route::middleware(['web', 'session:web'])->group(function () {

    Route::middleware('guest:web')->group(function () {
        Route::get('/login', function () { return view('auth'); })->name('login');
        Route::get('/register', function () { return view('auth'); })->name('register');
        
        // Gunakan controller yang sudah kita perbaiki
        Route::post('/register', [UserAuthController::class, 'register'])->name('register.submit');
        Route::post('/login', [UserAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:web')->group(function () {
        Route::post('/logout', function (Request $request) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/user/dashboard');
        })->name('logout');

        // Rute lain yang butuh login
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
        Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
        Route::put('/cart/update/{product}', [CartController::class, 'update'])->name('cart.update');
        Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout.index');
        Route::post('/checkout/store', [CartController::class, 'storeOrder'])->name('checkout.store');
        Route::get('/orders', [OrderController::class, 'history'])->name('orders.history');
        Route::get('/orders/status', [OrderController::class, 'status'])->name('orders.status');
        Route::put('/orders/{orderId}/upload-proof', [OrderController::class, 'uploadProof'])->name('orders.uploadProof');
    });
});


Route::prefix('admin')->name('admin.')->middleware(['web', 'session:admin'])->group(function () {
    
    Route::middleware('guest:admin')->group(function () {
        Route::get('/', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/', [AdminLoginController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth:admin', 'admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::resource('products', AdminProductController::class);
        Route::resource('orders', AdminOrderController::class);
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::delete('orders/{order}/remove-product/{product}', [AdminOrderController::class, 'removeProduct'])->name('orders.removeProduct');
        Route::get('/activities', [AdminController::class, 'activities'])->name('activities');
    });
});