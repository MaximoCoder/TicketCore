<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Ticket;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\TicketCategorieController;

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

    // Route::post('/broadcasting/auth', function () {
    //     return response()->json(['message' => 'Authenticated for broadcasting']);
    // })->name('broadcasting.auth');

    Route::post('checkToken', [AuthController::class, 'checkToken']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Dashsboard
    Route::get('/dashboard/getDashboardData', [DashboardController::class, 'getDashboardData']);

    // Tickets
    Route::post('/tickets/getPaginatedTickets', [TicketController::class, 'getPaginatedTickets']);
    Route::post('/tickets/getUserTickets', [TicketController::class, 'getUserTickets']);
    Route::post('/tickets/store', [TicketController::class, 'store']);
    Route::post('createTicket', [TicketController::class, 'createTicket']);
    Route::post('/tickets/getTicketById', [TicketController::class, 'getTicketById']);
    Route::post('/tickets/assignTicket', [TicketController::class, 'assignTicket']);
    Route::post('/tickets/unassignTicket', [TicketController::class, 'unassignTicket']);
    Route::post('/tickets/closeTicket', [TicketController::class, 'closeTicket']);
    Route::post('/tickets/getTicketComments', [TicketController::class, 'getTicketComments']);
    Route::post('/tickets/storeComment', [TicketController::class, 'storeComment']);

    // Categories
    Route::get('/categories/getAllCategories', [TicketCategorieController::class, 'getAllCategories']);
    // Route::post('getPaginatedCategories', [UserController::class, 'getPaginatedCategories']);
    // Route::post('createCategory', [UserController::class, 'createCategory']);
    // Route::post('getCategoryById', [UserController::class, 'getCategoryById']);
    // Route::post('updateCategoryById', [UserController::class, 'updateCategoryById']);
    // Route::post('deleteCategoryById', [UserController::class, 'deleteCategoryById']);

    // Departments
    Route::post('getPaginatedDepartments', [DepartmentController::class, 'getPaginatedDepartments']);
    Route::post('createDepartment', [DepartmentController::class, 'createDepartment']);
    Route::post('updateDepartmentById', [DepartmentController::class, 'updateDepartmentById']);
    Route::post('getDepartmentById', [DepartmentController::class, 'getDepartmentById']);
    Route::post('deleteDepartmentById', [DepartmentController::class, 'deleteDepartmentById']);

    // Users
    Route::get('getAllUsers', [UserController::class, 'getAllUsers']);
    Route::post('getPaginatedUsers', [UserController::class, 'getPaginatedUsers']);
    Route::post('createUser', [UserController::class, 'createUser']);
    Route::post('getUserById', [UserController::class, 'getUserById']);
    Route::post('updateUserById', [UserController::class, 'updateUserById']);
    Route::post('deleteUserById', [UserController::class, 'deleteUserById']);

    // Schedules
    Route::post('/schedules/getSchedule', [ScheduleController::class, 'getSchedule']);
    Route::post('/schedules/getByDate', [ScheduleController::class, 'getByDate']);
    Route::post('/schedules/update', [ScheduleController::class, 'updateSchedule']);
    Route::post('/schedules/delete', [ScheduleController::class, 'deleteDate']);
    Route::post('/schedule/unassign', [ScheduleController::class, 'unassignUserFromDate']);
    Route::post('/schedule/getScheduleCurrentWeek', [ScheduleController::class, 'getScheduleCurrentWeek']);

    // Faqs
    Route::post('/faqs/getPaginatedFaqs', [FaqController::class, 'getPaginatedFaqs']);
    Route::post('/faqs/createFaq', [FaqController::class, 'createFaq']);
    Route::post('/faqs/getFaqById', [FaqController::class, 'getFaqById']);
    Route::post('/faqs/updateFaqById', [FaqController::class, 'updateFaqById']);
    Route::post('/faqs/deleteFaqById', [FaqController::class, 'deleteFaqById']);
    Route::post('/faqs/getPublishedFaqsWithSteps', [FaqController::class, 'getPublishedFaqsWithSteps']);

    // Faq steps
    Route::post('/faqs/getPaginatedSteps', [FaqController::class, 'getPaginatedSteps']);
    Route::post('/faqs/createStepFaq', [FaqController::class, 'createStepFaq']);
    Route::post('/faqs/getStepFaqById', [FaqController::class, 'getStepFaqById']);
    Route::post('/faqs/updateStepFaqById', [FaqController::class, 'updateStepFaqById']);
    Route::post('/faqs/deleteStepFaqById', [FaqController::class, 'deleteStepFaqById']);
    Route::post('/faqs/getAllStepFaqByFaqId', [FaqController::class, 'getAllStepFaqByFaqId']);

    // WEBSOCKET
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    })->middleware(['auth:sanctum']);
});

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// Auth
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Departaments
Route::get('getAllDepartments', [DepartmentController::class, 'getAllDepartments']);
