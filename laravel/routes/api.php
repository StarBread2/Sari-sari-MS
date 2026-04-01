<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;

use App\Http\Controllers\ProductReorderLevelController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Categories TESTED
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

// Suppliers TESTED
Route::get('/suppliers', [SupplierController::class, 'index']);
Route::post('/suppliers', [SupplierController::class, 'store']);
Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);

// Stock Movements TESTED
Route::get('/stock-movements', [StockMovementController::class, 'index']);
Route::get('/stock-movements/{id}', [StockMovementController::class, 'show']);

// Products TESTED
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

// TESTED
// Sales DOUBLE CHECK LATER (BUSSINESS LOGIC)
// AFFECT Sales, SaleItem, Stock Movements
// NEEDS product.id, Stock_Movements (based as the stocks (needs atleast 1 stock with the ref product))
Route::get('/sales', [SaleController::class, 'index']);
Route::post('/sales', [SaleController::class, 'store']);
Route::get('/sales/{id}', [SaleController::class, 'show']);

// TESTED
// Purchases DOUBLE CHECK LATER (BUSSINESS LOGIC)
// AFFECT purchases, PurchaseItem,  Stock Movements, ProductPrice
// NEEDS product.id, supplier.id, 
Route::get('/purchases', [PurchaseController::class, 'index']);
Route::post('/purchases', [PurchaseController::class, 'store']);
Route::get('/purchases', [PurchaseController::class, 'index']);

//PRODUCT_REORDER_LEVELS
Route::prefix('reorder-levels')->group(function () {
    Route::get('/', [ProductReorderLevelController::class, 'index']);
    Route::get('/low-stock', [ProductReorderLevelController::class, 'lowStock']);
    Route::get('/{id}', [ProductReorderLevelController::class, 'show']);
    Route::post('/', [ProductReorderLevelController::class, 'store']);
    Route::put('/{id}', [ProductReorderLevelController::class, 'update']);
    Route::delete('/{id}', [ProductReorderLevelController::class, 'destroy']);
});