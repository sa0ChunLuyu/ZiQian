<?php

use Illuminate\Support\Facades\Route;

Route::any('api/yo', [\App\Http\Controllers\YoController::class, 'test']);
Route::get('/', function () {
    return view('welcome');
});
