<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ruta para obtener el token CSRF
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json([
        'message' => 'CSRF cookie set',
        'csrf_token' => csrf_token()
    ]);
})->middleware(['web']);
