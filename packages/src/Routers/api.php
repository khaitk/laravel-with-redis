<?php

use Laravel\Controllers\{
	ProductController
};
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::prefix('v1')->group(function () {
    Route::get('/version', function () {
        return response()->json(['version' => '1.0.0'], Response::HTTP_OK);
    });

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show'])->whereNumber('id');
});