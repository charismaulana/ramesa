<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\HistoricalController;
use App\Http\Controllers\BulkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LanguageController;

// Language switch route
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Home - redirect to login or dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authenticated routes
Route::middleware(['auth', 'role'])->group(function () {

    // Dashboard & Historical - accessible by all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/historical', [HistoricalController::class, 'index'])->name('historical.index');
    Route::get('/rooms', [\App\Http\Controllers\RoomDashboardController::class, 'index'])->name('rooms.dashboard');
    Route::get('/rooms/manage', [\App\Http\Controllers\RoomDashboardController::class, 'manage'])->name('rooms.manage');
    Route::get('/rooms/suggestions', [\App\Http\Controllers\RoomDashboardController::class, 'getRoomSuggestions'])->name('rooms.suggestions');
    Route::get('/rooms/export', [\App\Http\Controllers\RoomDashboardController::class, 'exportExcel'])->name('rooms.export');
    Route::post('/rooms/toggle-override', [\App\Http\Controllers\RoomDashboardController::class, 'toggleOverride'])->name('rooms.toggleOverride');
    Route::post('/rooms/groups', [\App\Http\Controllers\RoomDashboardController::class, 'storeGroup'])->name('rooms.storeGroup');
    Route::get('/rooms/groups/{id}/edit', [\App\Http\Controllers\RoomDashboardController::class, 'editGroup'])->name('rooms.editGroup');
    Route::put('/rooms/groups/{id}', [\App\Http\Controllers\RoomDashboardController::class, 'updateGroup'])->name('rooms.updateGroup');
    Route::delete('/rooms/groups/{id}', [\App\Http\Controllers\RoomDashboardController::class, 'destroyGroup'])->name('rooms.destroyGroup');
    Route::get('/rooms/room/{id}/edit', [\App\Http\Controllers\RoomDashboardController::class, 'editRoom'])->name('rooms.editRoom');
    Route::put('/rooms/room/{id}', [\App\Http\Controllers\RoomDashboardController::class, 'updateRoom'])->name('rooms.updateRoom');
    Route::delete('/rooms/room/{id}', [\App\Http\Controllers\RoomDashboardController::class, 'destroyRoom'])->name('rooms.destroyRoom');

    // Historical edit/delete routes (super_admin and tim_catering only)
    Route::middleware('role:super_admin,tim_catering')->group(function () {
        Route::get('/historical/{id}/edit', [HistoricalController::class, 'edit'])->name('historical.edit');
        Route::put('/historical/{id}', [HistoricalController::class, 'update'])->name('historical.update');
        Route::delete('/historical/{id}', [HistoricalController::class, 'destroy'])->name('historical.destroy');

        // Bulk edit routes
        Route::get('/historical/bulk-edit', [HistoricalController::class, 'bulkEditForm'])->name('historical.bulkEditForm');
        Route::post('/historical/bulk-edit', [HistoricalController::class, 'bulkEdit'])->name('historical.bulkEdit');
    });

    // Full access routes (super_admin and tim_catering only)
    Route::middleware('role:super_admin,tim_catering')->group(function () {
        // Scan routes
        Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
        Route::post('/scan/process', [ScanController::class, 'process'])->name('scan.process');
        Route::get('/scan/manual', [ScanController::class, 'manual'])->name('scan.manual');
        Route::post('/scan/manual', [ScanController::class, 'storeManual'])->name('scan.storeManual');

        // Bulk input routes
        Route::get('/bulk', [BulkController::class, 'index'])->name('bulk.index');
        Route::post('/bulk', [BulkController::class, 'store'])->name('bulk.store');

        // Employee routes
        Route::resource('employees', EmployeeController::class);
        Route::get('/employees/{employee}/print-card', [EmployeeController::class, 'printCard'])->name('employees.printCard');
        Route::get('/employees/{employee}/download-card', [EmployeeController::class, 'downloadCard'])->name('employees.downloadCard');

        // Export routes
        Route::get('/historical/export-form', [HistoricalController::class, 'exportForm'])->name('historical.exportForm');
        Route::match(['get', 'post'], '/historical/export', [HistoricalController::class, 'export'])->name('historical.export');
        Route::get('/historical/recap-export', [HistoricalController::class, 'recapExport'])->name('historical.recap');
        Route::get('/historical/recap-pdf', [HistoricalController::class, 'recapPDF'])->name('historical.recap-pdf');

        // Report routes
        Route::get('/report', [ReportController::class, 'form'])->name('report.form');
        Route::get('/report/generate', [ReportController::class, 'generate'])->name('report.generate');

        // Employee Group routes
        Route::get('/groups', [\App\Http\Controllers\EmployeeGroupController::class, 'index'])->name('groups.index');
        Route::post('/groups', [\App\Http\Controllers\EmployeeGroupController::class, 'store'])->name('groups.store');
        Route::put('/groups/{id}', [\App\Http\Controllers\EmployeeGroupController::class, 'update'])->name('groups.update');
        Route::delete('/groups/{id}', [\App\Http\Controllers\EmployeeGroupController::class, 'destroy'])->name('groups.destroy');

        // Bulk delete attendance route
        Route::post('/historical/bulk-delete', [HistoricalController::class, 'bulkDelete'])->name('historical.bulkDelete');

        // Download absence proofs as zip
        Route::post('/historical/download-proofs', [HistoricalController::class, 'downloadAbsenceProofs'])->name('historical.downloadProofs');

        // Get absence proofs API
        Route::get('/historical/absence-proofs', [HistoricalController::class, 'getAbsenceProofs'])->name('historical.getProofs');
    });

    // Super Admin only routes
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/admin/users/{user}/approve', [AdminController::class, 'approveUser'])->name('admin.approve');
        Route::delete('/admin/users/{user}', [AdminController::class, 'rejectUser'])->name('admin.reject');
        Route::patch('/admin/users/{user}/role', [AdminController::class, 'updateRole'])->name('admin.updateRole');

        // Meal prices route
        Route::post('/dashboard/prices', [DashboardController::class, 'updatePrices'])->name('dashboard.updatePrices');

        // Locked periods routes
        Route::get('/admin/locked-periods', [\App\Http\Controllers\LockedPeriodController::class, 'index'])->name('admin.lockedPeriods');
        Route::post('/admin/locked-periods', [\App\Http\Controllers\LockedPeriodController::class, 'store'])->name('admin.lockedPeriods.store');
        Route::delete('/admin/locked-periods/{id}', [\App\Http\Controllers\LockedPeriodController::class, 'destroy'])->name('admin.lockedPeriods.destroy');
    });
});
