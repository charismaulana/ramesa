<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are used by the POB application to fetch employee
| and attendance data from Ramesa.
|
*/

// Public auth routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Employees
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::get('/employees/departments', [EmployeeController::class, 'departments']);
    Route::get('/employees/locations', [EmployeeController::class, 'locations']);
    Route::get('/employees/{employee}', [EmployeeController::class, 'show']);

    // Attendances
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::get('/attendances/employee-ids', [AttendanceController::class, 'distinctEmployeeIds']);
});
