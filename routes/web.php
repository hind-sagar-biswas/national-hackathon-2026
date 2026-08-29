<?php

use App\Enums\Permission;
use App\Http\Controllers\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Admin\HoldController as AdminHoldController;
use App\Http\Controllers\Admin\ReconciliationController as AdminReconciliationController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MoneyRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('welcome');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/notifications', NotificationController::class)->middleware('throttle:notifications')->name('notifications.index');

    // Transfers
    Route::prefix('transfers')->name('transfers.')->controller(TransferController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_TRANSFERS->value);
        Route::post('/', 'store')->middleware(['can:'.Permission::CREATE_TRANSFERS->value, 'idempotent'])->name('store');
        Route::post('/resend-otp', 'resendOtp')->middleware(['can:'.Permission::CREATE_TRANSFERS->value, 'throttle:6,1'])->name('resend-otp');
    });

    // Money Requests
    Route::prefix('money-requests')->name('money-requests.')->controller(MoneyRequestController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_MONEY_REQUESTS->value);
        Route::post('/', 'store')->middleware(['can:'.Permission::CREATE_MONEY_REQUESTS->value, 'idempotent'])->name('store');
        Route::post('/{moneyRequest}/approve', 'approve')->middleware(['can:'.Permission::APPROVE_MONEY_REQUESTS->value, 'idempotent'])->name('approve');
        Route::post('/{moneyRequest}/reject', 'reject')->middleware('can:'.Permission::REJECT_MONEY_REQUESTS->value)->name('reject');
        Route::delete('/{moneyRequest}', 'destroy')->middleware('can:'.Permission::DELETE_MONEY_REQUESTS->value)->name('destroy');
    });

    // Peer-to-Peer Loans
    Route::prefix('loans')->name('loans.')->controller(LoanController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_LOANS->value);
        Route::get('/{loan}', 'show')->name('show')->middleware('can:'.Permission::VIEW_LOAN->value);
        Route::post('/', 'store')->middleware(['can:'.Permission::CREATE_LOANS->value, 'idempotent'])->name('store');
        Route::post('/{loan}/repay', 'repay')->middleware(['can:'.Permission::REPAY_LOANS->value, 'idempotent'])->name('repay');
        Route::post('/{loan}/waive', 'waive')->middleware('can:'.Permission::WAIVE_LOANS->value)->name('waive');
    });

    // Deposits (User)
    Route::prefix('deposits')->name('deposits.')->controller(DepositController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_DEPOSITS->value);
        Route::post('/', 'store')->middleware(['can:'.Permission::CREATE_DEPOSITS->value, 'idempotent'])->name('store');
    });

    // Transactions Explorer (User)
    Route::prefix('transactions')->name('transactions.')->controller(TransactionController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_TRANSACTIONS->value);
        Route::get('/{transaction}', 'show')->name('show')->middleware('can:'.Permission::VIEW_TRANSACTION->value);
    });

    // User Directory
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_USERS->value);
        Route::get('/{user}', 'show')->name('show')->middleware('can:'.Permission::VIEW_USER->value);
        if (config('app.feature.user_ban')) {
            Route::patch('/{user}/toggle', 'toggle')->name('toggle')->middleware(['can:'.Permission::TOGGLE_USERS->value, 'throttle:user-actions']);
        }
    });

    // Admin Banking & Oversight Portal
    Route::prefix('admin')->name('admin.')->group(function () {
        // Admin Deposit Approval Queue
        Route::prefix('deposits')->name('deposits.')->controller(AdminDepositController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_DEPOSITS->value);
            Route::post('/{depositRequest}/confirm', 'confirm')->middleware(['can:'.Permission::CONFIRM_DEPOSITS->value, 'idempotent'])->name('confirm');
            Route::post('/{depositRequest}/reject', 'reject')->middleware('can:'.Permission::REJECT_DEPOSITS->value)->name('reject');
        });

        // Admin Compliance Holds
        Route::prefix('holds')->name('holds.')->controller(AdminHoldController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_HOLDS->value);
            Route::post('/{hold}/release', 'release')->middleware('can:'.Permission::RELEASE_HOLDS->value)->name('release');
        });

        // Admin General Ledger Reconciliation
        Route::prefix('reconciliation')->name('reconciliation.')->controller(AdminReconciliationController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_RECONCILIATION->value);
            Route::post('/audit', 'audit')->middleware('can:'.Permission::RUN_RECONCILIATION_AUDIT->value)->name('audit');
            Route::post('/rollup', 'rollup')->middleware('can:'.Permission::RUN_RECONCILIATION_ROLLUP->value)->name('rollup');
        });

        // Admin Platform-wide Transactions Explorer
        Route::prefix('transactions')->name('transactions.')->controller(AdminTransactionController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:'.Permission::VIEW_ALL_TRANSACTIONS->value);
        });
    });
});
