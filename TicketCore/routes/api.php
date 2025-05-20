<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [AuthController::class, 'logout']);

    // Departments
    Route::post('getPaginatedDepartments', [DepartmentController::class, 'getPaginatedDepartments']);
    Route::post('createDepartment', [DepartmentController::class, 'createDepartment']);
    Route::post('show', [DepartmentController::class, 'show']);
    Route::post('update', [DepartmentController::class, 'update']);
    Route::post('deleteDepartment', [DepartmentController::class, 'deleteDepartment']);
});

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// Auth
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Departaments
Route::get('getAllDepartments', [DepartmentController::class, 'getAllDepartments']);
