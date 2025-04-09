<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Transformation\{
    TransferController,
    TransfersypController,
    ApprovalController,
    ExchangeController
};
use App\Http\Controllers\Transfers\{
    SentTransfersController,
    DeliverController,
    DeliveredTransfersController,
    ReceivedTransferController,
    SentTransfersApprovalController,
    ReceivedTransferApprovalController,
    RxchangeTransfersController,
    RxchangeReceivedTransferController
};
use App\Http\Controllers\{
    DestinationController,
    SubUserController,
    TransferReportController,
    Transfer2ReportController,
    BalanceController
};

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'throttle:60,1' // زيادة معدل الحد من الطلبات
])->group(function () {
    // Dashboard Routes
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Friend Request
    Route::view('/friend-request', 'student.friend-request')->name('student.friend-request');

    // Balances
    Route::get('/balances', [BalanceController::class, 'index'])->name('balances.index');

    // Transfers Group
    Route::prefix('transfers')->group(function () {
        // Main Transfers
        Route::post('/submit', [TransferController::class, 'store'])
            ->name('transfers.submit');
        Route::get('/get-destination-address', [TransferController::class, 'getDestinationAddress']);

        // Syrian Transfers
        Route::prefix('syp')->group(function () {
            Route::post('/submit', [TransfersypController::class, 'sypstore'])
                ->name('syp.submit');
            Route::get('/get-exchange-rate', [TransfersypController::class, 'getExchangeRate']);
        });

        // Exchange Rates
        Route::get('/get-exchange-rate', [ExchangeController::class, 'getExchangeRate'])
            ->name('exchange.getRate');
    });

    // Approvals
    Route::prefix('approvals')->group(function () {
        Route::get('/', [ApprovalController::class, 'create'])->name('approvals.create');
        Route::post('/submit', [ApprovalController::class, 'storeApproval'])
            ->middleware('throttle:10,1') // زيادة الحماية للطلبات الحساسة
            ->name('approvals.submit');
    });

    // Exchange
    Route::prefix('exchange')->group(function () {
        Route::get('/', [ExchangeController::class, 'create'])->name('exchange.create');
        Route::post('/submit', [ExchangeController::class, 'storeExchange'])
            ->name('exchange.submit');
        Route::post('/get-balance', [ExchangeController::class, 'getBalance'])
            ->name('exchange.getBalance');
    });

    // Destinations
    Route::resource('destinations', DestinationController::class)->except(['create', 'edit']);
    Route::prefix('destinations')->group(function () {
        Route::put('/{id}/update-limited', [DestinationController::class, 'updateLimited'])
            ->name('destinations.update-limited');
        Route::put('/{id}/update-password', [DestinationController::class, 'updatePassword'])
            ->name('destinations.update-password');
        Route::put('/{id}/toggle-stop-movements', [DestinationController::class, 'toggleStopMovements'])
            ->name('destinations.toggle-stop-movements');
        Route::get('/{id}/wages', [DestinationController::class, 'wages'])
            ->name('destinations.wages');
        Route::post('/wages/store', [DestinationController::class, 'storeWage'])
            ->name('destinations.wages.store');
    });

    // Sent Transfers
    Route::resource('sent-transfers', SentTransfersController::class)->except(['create', 'store']);
    Route::prefix('sent-transfers')->group(function () {
        Route::get('/data', [SentTransfersController::class, 'getTransfersData'])
            ->name('sent-transfers.data');
        Route::get('/{id}/print', [SentTransfersController::class, 'printImage'])
            ->name('sent-transfers.print');
        Route::get('/{id}/details', [SentTransfersController::class, 'getTransferDetails'])
            ->name('sent-transfers.details');
    });

    // Deliveries
    Route::resource('deliveries', DeliverController::class)->only(['index', 'show']);
    Route::get('/deliveries/{id}/details', [DeliverController::class, 'getTransferDetails'])
        ->name('deliveries.details');

    // Received Transfers
    Route::resource('received-transfers', ReceivedTransferController::class)->only(['index']);
    Route::prefix('received-transfers')->group(function () {
        Route::patch('/{transfer}/toggle-freeze', [ReceivedTransferController::class, 'toggleFreeze'])
            ->name('received-transfers.toggle-freeze');
        Route::post('/{transfer}/verify-password', [ReceivedTransferController::class, 'verifyPassword'])
            ->middleware('throttle:5,1')
            ->name('received-transfers.verify-password');
        Route::post('/{transfer}/deliver', [ReceivedTransferController::class, 'deliverTransfer'])
            ->name('received-transfers.deliver');
    });

    // Reports
    Route::get('/transfer-reports', [TransferReportController::class, 'index'])
        ->name('transfer-reports.index');
    Route::get('/transfer2-reports', [Transfer2ReportController::class, 'index'])
        ->name('transfer2-reports.index');

    // Additional Security Middleware for Critical Routes
    Route::middleware(['check.permissions'])->group(function () {
        // Approval Routes
        Route::resource('sent-approvals', SentTransfersApprovalController::class)->only(['index', 'destroy']);
        Route::resource('received-approvals', ReceivedTransferApprovalController::class)->only(['index']);

        // Exchange Transfers
        Route::resource('rxchange-transfers', RxchangeTransfersController::class)->only(['index', 'destroy']);
        Route::resource('rxchange-received', RxchangeReceivedTransferController::class)->only(['index', 'destroy']);
    });
});
