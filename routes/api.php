
<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDetailController;


// Auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Route untuk user yang sudah login
Route::middleware('auth:sanctum')->group(function () {
    // Logout user
    Route::post('logout', [AuthController::class, 'logout']);
    // Public user
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('products', [ProductController::class, 'index']);

    // Order user
    Route::get('myorders', [OrderController::class, 'myOrders']);
    Route::post('orders', [OrderController::class, 'store']);

    // Route khusus admin
    Route::middleware('role:admin')->group(function () {
        // Manajemen kategori & produk
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);

        // Route custom harus di atas resource agar tidak tertimpa
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);

        // Manajemen order & detail order
        Route::apiResource('orders', OrderController::class)->except(['store']);
        Route::apiResource('order-details', OrderDetailController::class);

        // Restore data
        Route::post('products/{id}/restore', [ProductController::class, 'restore']);
        Route::post('categories/{id}/restore', [CategoryController::class, 'restore']);
        Route::post('orders/{id}/restore', [OrderController::class, 'restore']);
        Route::post('order-details/{id}/restore', [OrderDetailController::class, 'restore']);
    });
});
