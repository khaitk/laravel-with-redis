<?php

use Laravel\Controllers\{
	TestController
};
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/version', function () {
        return "v1.0.0";
    });
});