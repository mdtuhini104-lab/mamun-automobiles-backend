<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/status', function () {
        return response()->json([
            'success' => true,
            'message' => 'API v1 is running',
        ]);
    });
    


    

    
    // Customer routes
    Route::get('/customers', [App\Http\Controllers\Api\v1\CustomerController::class, 'index']);
    Route::post('/customers', [App\Http\Controllers\Api\v1\CustomerController::class, 'store']);
    Route::get('/customers/{id}', [App\Http\Controllers\Api\v1\CustomerController::class, 'show']);
    
    // Vehicle routes
    Route::post('/vehicles', [App\Http\Controllers\Api\v1\VehicleController::class, 'store']);
    Route::get('/customers/{customerId}/vehicles', [App\Http\Controllers\Api\v1\VehicleController::class, 'index']);
    
    // Job Card routes
    Route::get('/job-cards', [App\Http\Controllers\Api\v1\JobCardController::class, 'index']);
    Route::post('/job-cards', [App\Http\Controllers\Api\v1\JobCardController::class, 'store']);
    Route::get('/job-cards/{id}', [App\Http\Controllers\Api\v1\JobCardController::class, 'show']);
    Route::get('/vehicles/{vehicleId}/job-cards', [App\Http\Controllers\Api\v1\JobCardController::class, 'vehicleHistory']);
    Route::get('/customers/{customerId}/job-cards', [App\Http\Controllers\Api\v1\JobCardController::class, 'customerHistory']);
    Route::put('/job-cards/{id}', [App\Http\Controllers\Api\v1\JobCardController::class, 'update']);
    Route::delete('/job-cards/{id}', [App\Http\Controllers\Api\v1\JobCardController::class, 'destroy']);
    
    // Parts routes
    Route::get('/parts/low-stock', [App\Http\Controllers\Api\v1\PartController::class, 'lowStock']);
    Route::get('/parts', [App\Http\Controllers\Api\v1\PartController::class, 'index']);
    Route::post('/parts', [App\Http\Controllers\Api\v1\PartController::class, 'store']);
    Route::get('/parts/{id}', [App\Http\Controllers\Api\v1\PartController::class, 'show']);
    Route::put('/parts/{id}', [App\Http\Controllers\Api\v1\PartController::class, 'update']);
    Route::delete('/parts/{id}', [App\Http\Controllers\Api\v1\PartController::class, 'destroy']);
    
    // Job Card Items routes
    Route::post('/job-cards/{id}/items', [App\Http\Controllers\Api\v1\JobCardItemController::class, 'store']);
    
    // Invoice routes
    Route::get('/invoices', [App\Http\Controllers\Api\v1\InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [App\Http\Controllers\Api\v1\InvoiceController::class, 'show']);
    Route::post('/invoices/{id}/pay', [App\Http\Controllers\Api\v1\InvoiceController::class, 'pay']);
    Route::get('/customers/{id}/due-invoices', [App\Http\Controllers\Api\v1\InvoiceController::class, 'customerDueInvoices']);

    // Supplier routes
    Route::get('/suppliers', [App\Http\Controllers\Api\v1\SupplierController::class, 'index']);
    Route::post('/suppliers', [App\Http\Controllers\Api\v1\SupplierController::class, 'store']);
    Route::get('/suppliers/{id}', [App\Http\Controllers\Api\v1\SupplierController::class, 'show']);
    Route::put('/suppliers/{id}', [App\Http\Controllers\Api\v1\SupplierController::class, 'update']);
    Route::delete('/suppliers/{id}', [App\Http\Controllers\Api\v1\SupplierController::class, 'destroy']);

    // Purchase routes
    Route::get('/purchases', [App\Http\Controllers\Api\v1\PurchaseController::class, 'index']);
    Route::post('/purchases', [App\Http\Controllers\Api\v1\PurchaseController::class, 'store']);
    Route::get('/purchases/{id}', [App\Http\Controllers\Api\v1\PurchaseController::class, 'show']);
    Route::put('/purchases/{id}/status', [App\Http\Controllers\Api\v1\PurchaseController::class, 'updateStatus']);
    Route::get('/purchases/low-stock-parts', [App\Http\Controllers\Api\v1\PurchaseController::class, 'lowStockParts']);
});
