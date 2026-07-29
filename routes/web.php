<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\TargetAlertController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ProjectController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated & Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard Overview
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & Password Change
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Feature 4: Interactive Renewal Timeline Calendar & iCal Export
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/export.ics', [CalendarController::class, 'exportIcal'])->name('calendar.export-ical');

    // Feature 5: Team & Project Cost Allocation & Tax Reports
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/export-tax-report', [ProjectController::class, 'exportTaxReport'])->name('projects.export-tax');
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::post('/projects/allocate', [ProjectController::class, 'allocate'])->name('projects.allocate');
    });

    // Subscriptions
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::post('/subscriptions/{id}/status', [SubscriptionController::class, 'updateStatus'])->name('subscriptions.update-status');
    });

    // Token & Usage Ledger
    Route::get('/usage', [UsageController::class, 'index'])->name('usage.index');
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/usage/store', [UsageController::class, 'storeUsage'])->name('usage.store');
        Route::post('/usage/package', [UsageController::class, 'storePackage'])->name('usage.store-package');
    });

    // Payment Accounts
    Route::get('/payment-accounts', [PaymentAccountController::class, 'index'])->name('payment-accounts.index');
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/payment-accounts', [PaymentAccountController::class, 'store'])->name('payment-accounts.store');
    });
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/payment-accounts/{id}/replace', [PaymentAccountController::class, 'replace'])->name('payment-accounts.replace');
    });

    // Budgets & Alerts
    Route::get('/targets', [TargetAlertController::class, 'index'])->name('targets.index');
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/targets', [TargetAlertController::class, 'storeTarget'])->name('targets.store');
        Route::post('/alerts/{id}/acknowledge', [TargetAlertController::class, 'acknowledgeAlert'])->name('alerts.acknowledge');
    });

    // Import & Audit Trail
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/import', [ImportController::class, 'process'])->name('import.process');
    });

    // System Administration Modules (ADMIN ONLY)
    Route::middleware(['role:admin'])->group(function () {
        // Feature 3: Multi-Channel Webhooks
        Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
        Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
        Route::post('/webhooks/{id}/test', [WebhookController::class, 'test'])->name('webhooks.test');
        Route::delete('/webhooks/{id}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');

        // Workspace Settings & System Config
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/workspace', [SettingsController::class, 'updateWorkspace'])->name('settings.update-workspace');
        Route::post('/settings/categories', [SettingsController::class, 'storeCategory'])->name('settings.store-category');
        Route::delete('/settings/categories/{id}', [SettingsController::class, 'deleteCategory'])->name('settings.delete-category');
        Route::post('/settings/vendors', [SettingsController::class, 'storeVendor'])->name('settings.store-vendor');
        Route::delete('/settings/vendors/{id}', [SettingsController::class, 'deleteVendor'])->name('settings.delete-vendor');
        Route::post('/settings/tools', [SettingsController::class, 'storeTool'])->name('settings.store-tool');
        Route::delete('/settings/tools/{id}', [SettingsController::class, 'deleteTool'])->name('settings.delete-tool');
        Route::post('/settings/meter-units', [SettingsController::class, 'storeMeterUnit'])->name('settings.store-meter-unit');
        Route::delete('/settings/meter-units/{id}', [SettingsController::class, 'deleteMeterUnit'])->name('settings.delete-meter-unit');

        // User Management Directory & Roles
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{id}/role', [UserController::class, 'updateRole'])->name('users.update-role');
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{id}/regenerate-password', [UserController::class, 'regeneratePassword'])->name('users.regenerate-password');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Granular Role Permission Matrix
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions', [PermissionController::class, 'updateMatrix'])->name('permissions.update');
    });
});
