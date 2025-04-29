<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Transformation\TransferController;
use App\Http\Controllers\Transformation\TransfersypController;
use App\Http\Controllers\Transformation\ApprovalController;
use App\Http\Controllers\Transformation\ExchangeController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\Transfers\SentTransfersController;
use App\Http\Controllers\Transfers\DeliverController;
use App\Http\Controllers\Transfers\DeliveredTransfersController;
use App\Http\Controllers\Transfers\ReceivedTransferController;
use App\Http\Controllers\Transfers\SentTransfersApprovalController;
use App\Http\Controllers\Transfers\ReceivedTransferApprovalController;
use App\Http\Controllers\Transfers\RxchangeTransfersController;
use App\Http\Controllers\Transfers\RxchangeReceivedTransferController;
use App\Http\Controllers\SubUserController;
use App\Http\Controllers\TransferReportController;
use App\Http\Controllers\Transfer2ReportController;
use App\Http\Controllers\BalanceController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // واجهة لوحة التحكم الرئيسية
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/friend-request', function () {
        return view('student.friend-request');
    })->name('student.friend-request');

    // عرض رصيد الحساب
    Route::get('/balances', [BalanceController::class, 'index'])->name('balances.index');

    // عمليات التحويل (TransferController)
    Route::get('/dashboard/transfer', [TransferController::class, 'create'])->name('transfer.create');
    Route::post('/dashboard/transfer/submit', [TransferController::class, 'store'])->name('transfer.submit');
    Route::get('/get-destination-address', [TransferController::class, 'getDestinationAddress']);

    // تحويل بالعملة السورية (TransfersypController)
    Route::get('/dashboard/syp-transfer', [TransfersypController::class, 'create'])->name('syp.create');
    Route::post('/syp/submit', [TransfersypController::class, 'sypstore'])->name('syp.submit');
    Route::get('/get-exchange-rate', [TransfersypController::class, 'getExchangeRate']);

    // سعر الصرف (ExchangeController)
    Route::get('/exchange-rate', [ExchangeController::class, 'getExchangeRate'])->name('exchange.getRate');

    // الموافقات
    Route::get('/approval', [ApprovalController::class, 'create'])->name('approval.create');
    Route::post('/approval/submit', [ApprovalController::class, 'storeApproval'])->name('approval.submit');

    // الصرافة
    Route::get('/exchange', [ExchangeController::class, 'create'])->name('exchange.create');
    Route::post('/exchange/submit', [ExchangeController::class, 'storeExchange'])->name('exchange.submit');
    Route::post('/exchange/get-balance', [ExchangeController::class, 'getBalance'])->name('exchange.getBalance');

    // الوجهات
    Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
    Route::get('/destination/{id}/wages', [DestinationController::class, 'wages'])->name('destination.wages');
    Route::put('/update-limited/{id}', [DestinationController::class, 'updateLimited'])->name('update.limited');
    Route::put('/update-password/{id}', [DestinationController::class, 'updatePassword'])->name('update.password');
    Route::put('/toggle-stop-movements/{id}', [DestinationController::class, 'toggleStopMovements'])->name('toggle.stop.movements');
    Route::post('/wages/store', [DestinationController::class, 'store'])->name('wages.store');

    // الحوالات المرسلة
    Route::get('/transfers/sent', [SentTransfersController::class, 'index'])->name('transfers.sent.index');
    Route::get('/transfers/sent/data', [SentTransfersController::class, 'getTransfersData'])->name('transfers.sent.data');
    Route::get('/transfers/sent/{id}/edit', [SentTransfersController::class, 'edit'])->name('transfers.sent.edit');
    Route::put('/transfers/sent/{id}', [SentTransfersController::class, 'update'])->name('transfers.sent.update');
    Route::delete('/transfers/sent/{id}', [SentTransfersController::class, 'destroy'])->name('transfers.sent.destroy');
    Route::get('/transfers/sent/{id}/print', [SentTransfersController::class, 'printImage'])->name('transfers.sent.print');
    Route::get('/transfers/sent/{id}/details', [SentTransfersController::class, 'getTransferDetails'])->name('transfers.sent.details');

    // الحوالات للتسليم
    Route::get('/transfers/deliver', [DeliverController::class, 'index'])->name('deliver.index');
    Route::get('/transfers/deliver/{id}/details', [DeliverController::class, 'getTransferDetails'])->name('deliver.details');

    // الحوالات المستلمة
    Route::get('/transfers/received', [ReceivedTransferController::class, 'index'])->name('transfers.received');
    Route::patch('/transfers/{transfer}/toggle-freeze', [ReceivedTransferController::class, 'toggleFreeze'])->name('transfers.toggle-freeze');
    Route::post('/transfers/{transfer}/verify-password', [ReceivedTransferController::class, 'verifyPassword'])->middleware('throttle:5,1')->name('transfers.verify-password');
    Route::post('/transfers/{transfer}/deliver', [ReceivedTransferController::class, 'deliverTransfer'])->name('transfers.deliver');

    // اعتماد الحوالات الصادرة
    Route::get('/transfers/sentapproval', [SentTransfersApprovalController::class, 'index'])->name('transfers.sentapproval');
    Route::get('/transfers/sentapproval/data', [SentTransfersApprovalController::class, 'getTransfersData'])->name('transfers.sentapproval.data');
    Route::delete('/transfers/sentapproval/{id}', [SentTransfersApprovalController::class, 'destroy'])->name('transfers.sentapproval.destroy');
    Route::get('/transfers/sentapproval/{id}/print', [SentTransfersApprovalController::class, 'printImage'])->name('transfers.sentapproval.print');
    Route::get('/transfers/sentapproval/{id}/details', [SentTransfersApprovalController::class, 'getTransferDetails'])->name('transfers.sentapproval.details');

    // اعتماد الحوالات الواردة
    Route::get('/transfers/receivedapproval', [ReceivedTransferApprovalController::class, 'index'])->name('transfers.receivedapproval');
    Route::post('/transfers/{transfer}/verify-password', [ReceivedTransferApprovalController::class, 'verifyPassword'])->middleware('throttle:5,1')->name('transfers.receivedapproval.verify-password');
    Route::post('/transfers/{transfer}/deliverr', [ReceivedTransferApprovalController::class, 'deliverTransferr'])->name('transfers.receivedapproval.deliver');

    // قص صادر
    Route::get('/transfers/sentrxchangeTransfers', [RxchangeTransfersController::class, 'index'])->name('transfers.sentrxchange');
    Route::get('/transfers/sentrxchange/data', [RxchangeTransfersController::class, 'getTransfersData'])->name('transfers.sentrxchange.data');
    Route::delete('/transfers/sentrxchange/{id}', [RxchangeTransfersController::class, 'destroy'])->name('transfers.sentrxchange.destroy');
    Route::get('/transfers/sentrxchange/{id}/print', [RxchangeTransfersController::class, 'printImage'])->name('transfers.sentrxchange.print');
    Route::get('/transfers/sentrxchange/{id}/details', [RxchangeTransfersController::class, 'getTransferDetails'])->name('transfers.sentrxchange.details');

    // قص وارد
    Route::get('/transfers/rxchangeReceivedTransfer', [RxchangeReceivedTransferController::class, 'index'])->name('transfers.rxchangeReceivedTransfer');
    Route::get('/transfers/rxchangeReceivedTransfer/data', [RxchangeReceivedTransferController::class, 'getTransfersData'])->name('transfers.rxchangeReceivedTransfer.data');
    Route::delete('/transfers/rxchangeReceivedTransfer/{id}', [RxchangeReceivedTransferController::class, 'destroy'])->name('transfers.rxchangeReceivedTransfer.destroy');
    Route::get('/transfers/rxchangeReceivedTransfer/{id}/print', [RxchangeReceivedTransferController::class, 'printImage'])->name('transfers.rxchangeReceivedTransfer.print');
    Route::get('/transfers/rxchangeReceivedTransfer/{id}/details', [RxchangeReceivedTransferController::class, 'getTransferDetails'])->name('transfers.rxchangeReceivedTransfer.details');
    Route::post('/transfers/rxchangeReceivedTransfer/{transfer}/verify-password', [RxchangeReceivedTransferController::class, 'verifyPassword'])->name('transfers.rxchangeReceivedTransfer.verify-password');
    Route::post('/transfers/rxchangeReceivedTransfer/{transfer}/deliver', [RxchangeReceivedTransferController::class, 'deliverTransfer'])->name('transfers.rxchangeReceivedTransfer.deliver');

    // كشف حساب
    Route::get('/transfers', [TransferReportController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/report', [Transfer2ReportController::class, 'index'])->name('transfers.Transfer2Report');

});
