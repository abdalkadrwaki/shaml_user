<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Transformation\TransferController;
use App\Http\Controllers\Transformation\TransfersypController;
use App\Http\Controllers\Transformation\ApprovalController;
use App\Http\Controllers\Transformation\ExchangeController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\Transfers\SentTransfersController;
use App\Http\Controllers\Transfers\ReceivedTransferController;
use App\Http\Controllers\Transfers\SentTransfersApprovalController;
use App\Http\Controllers\Transfers\ReceivedTransferApprovalController;
use App\Http\Controllers\Transfers\RxchangeTransfersController;
use App\Http\Controllers\Transfers\RxchangeReceivedTransferController;
use App\Http\Controllers\SubUserController;
use App\Http\Controllers\TransferReportController; // Route لتسجيل الخروج
use App\Http\Controllers\BalanceController;
Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


    Route::get('/friend-request', function () {
        return view('student.friend-request');
    })->name('student.friend-request');








    /// حساب فرعي
 //Route::get('/sub-users/create', [SubUserController::class, 'create'])->name('sub-users.create');
 //Route::post('/sub-users', [SubUserController::class, 'store']);
 Route::get('dashboard', [TransferController::class, 'create'])->name('dashboard');
 Route::get('/balances', [BalanceController::class, 'index'])->name('balances.index');
 // مسارات نقل الأموال

 Route::post('dashboard/transfer/submit', [TransferController::class, 'store'])->name('dashboard.transfer.submit');
 Route::get('/get-destination-address', [TransferController::class, 'getDestinationAddress']);

 // تحويل السوري
 Route::get('syp', [TransfersypController::class, 'create'])->name('syp.create');
 Route::post('syp/submit', [TransfersypController::class, 'sypstore'])->name('syp.submit');

 Route::get('get-exchange-rate', [ExchangeController::class, 'getExchangeRate'])->name('exchange.getRate');
 Route::get('dashboard', [TransfersypController::class, 'getExchangeRate']);
 // مسارات الموافقات

 Route::get('approval', [ApprovalController::class, 'create'])->name('approval.create');
 Route::post('approval/submit', [ApprovalController::class, 'storeApproval'])->name('approval.submit');

 // مسارات صرافة
 Route::get('exchange', [ExchangeController::class, 'create'])->name('exchange.create');
 Route::post('exchange/submit', [ExchangeController::class, 'storeExchange'])->name('exchange.submit');
 Route::post('/exchange/get-balance', [ExchangeController::class, 'getBalance'])->name('exchange.getBalance');

 // مسارات الوجهات
 Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
 // حذف تعريف المسار المكرر:
 // Route::get('/destination/{id}', [DestinationController::class, 'wages'])->name('destination.wages');
 Route::put('/update-limited/{id}', [DestinationController::class, 'updateLimited'])->name('update.limited');
 Route::put('/update-password/{id}', [DestinationController::class, 'updatePassword'])->name('update.password');

 Route::put('/toggle-stop-movements/{id}', [DestinationController::class, 'toggleStopMovements'])->name('toggle.stop.movements');
 Route::get('/destination/{id}/wages', [DestinationController::class, 'wages'])->name('destination.wages');
 Route::post('/wages/store', [DestinationController::class, 'store'])->name('wages.store');

 // مسارات الحوالات المرسلة (SentTransfersController)
 Route::get('/transfers/sent', [SentTransfersController::class, 'index'])->name('transfers.sent.index');
 Route::get('/transfers/sent/data', [SentTransfersController::class, 'getTransfersData'])->name('transfers.sent.data');
 Route::get('/transfers/sent/{id}/edit', [SentTransfersController::class, 'edit'])->name('transfers.sent.edit');
 Route::put('/transfers/sent/{id}', [SentTransfersController::class, 'update'])->name('transfers.sent.update');
 Route::delete('/transfers/sent/{id}', [SentTransfersController::class, 'destroy'])->name('transfers.sent.destroy');
 Route::get('/transfers/sent/{id}/print', [SentTransfersController::class, 'printImage'])->name('transfers.sent.print');
 Route::get('/transfers/sent/{id}/details', [SentTransfersController::class, 'getTransferDetails'])->name('transfers.sent.details');

 // مسار الحوالات الواردة
 Route::get('/transfers/received', [ReceivedTransferController::class, 'index'])
     ->name('transfers.received');
 Route::patch('/transfers/{transfer}/toggle-freeze', [ReceivedTransferController::class, 'toggleFreeze'])
     ->name('transfers.toggle-freeze');
 Route::post('/transfers/{transfer}/verify-password', [ReceivedTransferController::class, 'verifyPassword'])
     ->middleware('throttle:5,1')
     ->name('transfers.verify-password');
 Route::post('/transfers/{transfer}/deliver', [ReceivedTransferController::class, 'deliverTransfer'])
     ->name('transfers.deliver');

 // مسار اعتمادات صادرة (SentTransfersApprovalController)

 Route::get('/transfers/sentapproval', [SentTransfersApprovalController::class, 'index'])
     ->name('transfers.sentapproval');
 Route::get('/transfers/sentapproval/data', [SentTransfersApprovalController::class, 'getTransfersData'])
     ->name('transfers.sentapproval.data');
 Route::delete('/transfers/sentapproval/{id}', [SentTransfersApprovalController::class, 'destroy'])
     ->name('transfers.sentapproval.destroy');
 Route::get('/transfers/sentapproval/{id}/print', [SentTransfersApprovalController::class, 'printImage'])
     ->name('transfers.sentapproval.print');
 Route::get('/transfers/sentapproval/{id}/details', [SentTransfersApprovalController::class, 'getTransferDetails'])
     ->name('transfers.sentapproval.details');


 // مسار اعتمادات واردة (ReceivedTransferApprovalController)

 Route::get('/transfers/receivedapproval', [ReceivedTransferApprovalController::class, 'index'])
     ->name('transfers.receivedapproval');
 Route::post('/transfers/{transfer}/verify-password', [ReceivedTransferApprovalController::class, 'verifyPassword'])
     ->middleware('throttle:5,1')
     ->name('transfers.verify-password');
 Route::post('/transfers/{transfer}/deliverr', [ReceivedTransferApprovalController::class, 'deliverTransferr'])
     ->name('transfers.deliverr');

 // قص صادر



 Route::get('/transfers/sentrxchangeTransfers', [RxchangeTransfersController::class, 'index'])
     ->name('transfers.sentrxchange');

 Route::get('/transfers/sentrxchange/data', [RxchangeTransfersController::class, 'getTransfersData'])
     ->name('transfers.sentrxchange.data');

 Route::delete('/transfers/sentrxchange/{id}', [RxchangeTransfersController::class, 'destroy'])
     ->name('transfers.sentrxchange.destroy');

 Route::get('/transfers/sentrxchange/{id}/print', [RxchangeTransfersController::class, 'printImage'])
     ->name('transfers.sentrxchange.print');

 Route::get('/transfers/sentrxchange/{id}/details', [RxchangeTransfersController::class, 'getTransferDetails'])
     ->name('transfers.sentrxchange.details');


 ///وارد القص


 Route::get('/transfers/rxchangeReceivedTransfer', [RxchangeReceivedTransferController::class, 'index'])
     ->name('transfers.rxchangeReceivedTransfer');

 Route::get('/transfers/rxchangeReceivedTransfer/data', [RxchangeReceivedTransferController::class, 'getTransfersData'])
     ->name('transfers.rxchangeReceivedTransfer.data');

 Route::delete('/transfers/rxchangeReceivedTransfer/{id}', [RxchangeReceivedTransferController::class, 'destroy'])
     ->name('transfers.rxchangeReceivedTransfer.destroy');

 Route::get('/transfers/rxchangeReceivedTransfer/{id}/print', [RxchangeReceivedTransferController::class, 'printImage'])
     ->name('transfers.rxchangeReceivedTransfer.print');

 Route::get('/transfers/rxchangeReceivedTransfer/{id}/details', [RxchangeReceivedTransferController::class, 'getTransferDetails'])
     ->name('transfers.rxchangeReceivedTransfer.details');

 // إضافة مسارات التحقق وتسليم الحوالة
 Route::post('/transfers/rxchangeReceivedTransfer/{transfer}/verify-password', [RxchangeReceivedTransferController::class, 'verifyPassword'])
     ->name('transfers.rxchangeReceivedTransfer.verify-password');

 Route::post('/transfers/rxchangeReceivedTransfer/{transfer}/deliver', [RxchangeReceivedTransferController::class, 'deliverTransfer'])
     ->name('transfers.rxchangeReceivedTransfer.deliver');
 /// كشف حساب

 Route::get('/transfers', [TransferReportController::class, 'index'])->name('transfers.index');










});
