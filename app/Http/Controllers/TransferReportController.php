<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Currency;

class TransferReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedCurrency = $request->currency;
        $currencies = Currency::activeCurrencies();
        $currencyNames = $currencies->pluck('name_ar', 'name_en')->toArray();

        // تهيئة خريطة الأرصدة للعملة المحددة فقط
        $balanceCurrencies = $selectedCurrency ? [$selectedCurrency] : [];
        $balanceMap = array_fill_keys($balanceCurrencies, 0);
        $transferData = [];

        if ($request->hasAny(['currency', 'from_date', 'to_date'])) {

            // حساب الرصيد الابتدائي للعملة المحددة (المعاملات التي تسبق تاريخ البحث)
            if ($request->filled('from_date') && $selectedCurrency) {
                try {
                    $fromDate = Carbon::parse($request->from_date)->startOfDay();

                    $initialQuery = Transfer::where(function ($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhere('destination', Auth::id());
                    })
                    ->where('created_at', '<', $fromDate)
                    ->whereIn('transaction_type', ['Transfer', 'Exchange', 'Credit'])
                    ->where(function ($q) use ($selectedCurrency) {
                        $q->where('sent_currency', $selectedCurrency)
                          ->orWhere('received_currency', $selectedCurrency);
                    });

                    $initialTransactions = $initialQuery->orderBy('created_at')->get();

                    foreach ($initialTransactions as $transfer) {
                        $this->updateBalanceMap($balanceMap, $transfer, $selectedCurrency);
                    }
                } catch (\Exception $e) {
                    // معالجة الأخطاء إذا لزم الأمر
                }
            }

            // حفظ رصيد أول المدة
            $initialBalance = $balanceMap[$selectedCurrency] ?? 0;

            // استعلام المعاملات ضمن الفترة المطلوبة
            $query = Transfer::where(function ($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhere('destination', Auth::id());
                    })
                    ->whereIn('transaction_type', ['Transfer', 'Exchange', 'Credit']);

            if ($selectedCurrency) {
                $query->where(function ($q) use ($selectedCurrency) {
                    $q->where('sent_currency', $selectedCurrency)
                      ->orWhere('received_currency', $selectedCurrency);
                });
            }

            if ($request->filled('from_date') && $request->filled('to_date')) {
                try {
                    $fromDate = Carbon::parse($request->from_date)->startOfDay();
                    $toDate = Carbon::parse($request->to_date)->endOfDay();
                    $query->whereBetween('created_at', [$fromDate, $toDate]);
                } catch (\Exception $e) {
                    // معالجة الأخطاء إذا لزم الأمر
                }
            }

            $transfers = $query->orderBy('created_at')->get();

            // معالجة معاملات الفترة
            foreach ($transfers as $transfer) {
                $this->processTransfer($transfer, $balanceMap, $transferData, $selectedCurrency);
            }

            // الرصيد النهائي بعد معاملات الفترة
            $finalBalance = $balanceMap[$selectedCurrency] ?? 0;
        } else {
            $initialBalance = 0;
            $finalBalance = 0;
        }

        return view('transfers.index', compact(
            'transferData',
            'currencies',
            'currencyNames',
            'selectedCurrency',
            'initialBalance',
            'finalBalance'
        ));
    }

    private function updateBalanceMap(array &$balanceMap, Transfer $transfer, ?string $currencyFilter = null)
    {
        $currenciesToUpdate = $currencyFilter ? [$currencyFilter] : array_keys($balanceMap);

        if ($transfer->transaction_type === 'Transfer') {
            $this->handleTransfer($balanceMap, $transfer, $currenciesToUpdate);
        } elseif ($transfer->transaction_type === 'Exchange' && $transfer->status === 'Delivered') {
            $this->handleExchange($balanceMap, $transfer, $currenciesToUpdate);
        } elseif ($transfer->transaction_type === 'Credit' && $transfer->status === 'Delivered') {
            $this->handleCredit($balanceMap, $transfer, $currenciesToUpdate);
        }
    }

    private function handleTransfer(&$balanceMap, $transfer, $currenciesToUpdate)
    {
        $senderCurrency = $transfer->sent_currency;
        $receiverCurrency = $transfer->received_currency;

        if ($transfer->status === 'Archived') {
            if ($transfer->user_id == Auth::id() && in_array($senderCurrency, $currenciesToUpdate)) {
                $balanceMap[$senderCurrency] += ($transfer->sent_amount + $transfer->fees);
            }
            if ($transfer->destination == Auth::id() && in_array($receiverCurrency, $currenciesToUpdate)) {
                $balanceMap[$receiverCurrency] -= ($transfer->received_amount + $transfer->fees);
            }
        } else {
            if ($transfer->user_id == Auth::id() && in_array($senderCurrency, $currenciesToUpdate)) {
                $balanceMap[$senderCurrency] -= ($transfer->sent_amount + $transfer->fees);
            }
            if ($transfer->destination == Auth::id() && in_array($receiverCurrency, $currenciesToUpdate)) {
                $balanceMap[$receiverCurrency] += ($transfer->received_amount + $transfer->fees);
            }
        }
    }

    private function handleExchange(&$balanceMap, $transfer, $currenciesToUpdate)
    {
        // هل المستخدم الحالي هو الذي أنشأ الحوالة (المرسِل)؟
        $userIsSender = ($transfer->user_id == Auth::id());

        // نعالج فقط إذا كانت العملية ناجحة (Delivered)
        if ($transfer->status === 'Delivered') {
            if ($userIsSender) {
                // إذا كان المستخدم هو المرسل: يخصم من العملة المُرسلة ويضاف للعملة المستلمة
                if (in_array($transfer->sent_currency, $currenciesToUpdate)) {
                    $balanceMap[$transfer->sent_currency] -= ($transfer->sent_amount + $transfer->fees);
                }
                if (in_array($transfer->received_currency, $currenciesToUpdate)) {
                    $balanceMap[$transfer->received_currency] += $transfer->received_amount;
                }
            } else {
                // إذا كان المستخدم هو المستقبِل في عملية الصرافة
                // هنا نعكس المنطق لأن المبلغ المستلم هو sent_currency
                // والمبلغ المدفوع هو received_currency من وجهة نظر هذا المستخدم
                if (in_array($transfer->sent_currency, $currenciesToUpdate)) {
                    // في هذه الحالة المستخدم يستلم العملة التي في خانة sent_currency
                    $balanceMap[$transfer->sent_currency] += $transfer->sent_amount;
                }
                if (in_array($transfer->received_currency, $currenciesToUpdate)) {
                    // ويخصم من العملة الموجودة في received_currency + الرسوم
                    $balanceMap[$transfer->received_currency] -= ($transfer->received_amount + $transfer->fees);
                }
            }
        }
    }

    private function handleCredit(&$balanceMap, $transfer, $currenciesToUpdate)
    {
        if (in_array($transfer->sent_currency, $currenciesToUpdate)) {
            $balanceMap[$transfer->sent_currency] += $transfer->sent_amount;
        }
    }

    private function processTransfer($transfer, &$balanceMap, &$transferData, $selectedCurrency)
    {
        $previousBalance = $selectedCurrency ? ($balanceMap[$selectedCurrency] ?? 0) : 0;

        $this->updateBalanceMap($balanceMap, $transfer, $selectedCurrency);

        $currentBalance = $selectedCurrency ? ($balanceMap[$selectedCurrency] ?? 0) : 0;

        $this->buildTransferRow(
            $transfer,
            $transferData,
            $selectedCurrency,
            $previousBalance,
            $currentBalance
        );
    }
    private function buildTransferRow($transfer, &$transferData, $currencyFilter, $prevBalance, $currentBalance)
    {
        $operations = [];

        if ($transfer->transaction_type === 'Exchange' && $transfer->status === 'Delivered') {
            $this->addExchangeOperations($transfer, $operations, $currencyFilter);
        } elseif ($transfer->transaction_type === 'Credit' && $transfer->status === 'Delivered') {
            $this->addCreditOperation($transfer, $operations, $currencyFilter);
        } else {
            $this->addTransferOperation($transfer, $operations, $currencyFilter);
        }

        foreach ($operations as $operation) {
            $transferData[] = [
                'transfer' => $transfer,
                'operation' => $operation['type'],
                'amount' => $operation['amount'],
                'currency' => $operation['currency'],
                'cumulative_balance' => $currentBalance,
            ];
        }
    }

    private function addExchangeOperations($transfer, &$operations, $currencyFilter)
    {
        // هل المستخدم الحالي هو الذي أنشأ الحوالة (المرسِل)؟
        $userIsSender = ($transfer->user_id == Auth::id());

        // إن لم يكن هناك فلتر عملة أو كان يطابق العملة المرسلة
        if (!$currencyFilter || $currencyFilter === $transfer->sent_currency) {
            $operations[] = [
                // إن كان المرسل هو المستخدم الحالي، فهو يبيع العملة المرسلة
                // أما إن كان هو المستقبِل، فهو يشتري هذه العملة
                'type'   => $userIsSender ? 'sell' : 'buy',
                'amount' => $userIsSender
                    ? - ($transfer->sent_amount + $transfer->fees) // يخصم إن كان مرسلاً
                    :  $transfer->sent_amount,                    // يضيف إن كان مستقبلاً
                'currency' => $transfer->sent_currency,
            ];
        }

        // إن لم يكن هناك فلتر عملة أو كان يطابق العملة المستلمة
        if (!$currencyFilter || $currencyFilter === $transfer->received_currency) {
            $operations[] = [
                // إن كان المستخدم هو المرسل، فهو يشتري العملة المستلمة
                // وإن كان مستقبلاً، فهو يبيع هذه العملة (من وجهة نظره)
                'type'   => $userIsSender ? 'buy' : 'sell',
                'amount' => $userIsSender
                    ?  $transfer->received_amount                // يضاف إن كان مرسلاً
                    : - ($transfer->received_amount + $transfer->fees), // يخصم إن كان مستقبلاً
                'currency' => $transfer->received_currency,
            ];
        }
    }

    private function addCreditOperation($transfer, &$operations, $currencyFilter)
    {
        if (!$currencyFilter || $currencyFilter === $transfer->sent_currency) {
            $operations[] = [
                'type' => 'credit',
                'amount' => $transfer->sent_amount,
                'currency' => $transfer->sent_currency
            ];
        }
    }

    private function addTransferOperation($transfer, &$operations, $currencyFilter)
    {
        // تحديد هل المستخدم الحالي هو المرسل أم المستقبل
        $userIsSender = ($transfer->user_id == Auth::id());

        // تحديد العملة المستخدمة حسب إن كان المستخدم هو المرسل أم المستقبل
        $currency = $userIsSender ? $transfer->sent_currency : $transfer->received_currency;

        // لو كان هناك فلتر للعملة وكان لا يطابق عملة هذه العملية، نخرج مباشرة
        if ($currencyFilter && $currency !== $currencyFilter) {
            return;
        }

        // التحقق من حالة الحوالة
        if ($transfer->status === 'Archived') {
            // إذا كانت الحوالة ملغاة، نعكس الإشارة:
            // المرسل (الذي كان مدينًا) يصبح دائنًا، والمستقبل يصبح مدينًا.
            $amount = $userIsSender
                ? ($transfer->sent_amount) // استرداد للمرسل
                : - ($transfer->received_amount);             // خصم من المستقبل

            // مجرد تسمية لتمييز نوع العملية في الجدول
            $type = $userIsSender ? 'ملغاة (مستردة)' : 'ملغاة (مخصومة)';
        } else {
            // الحالة العادية (الحوالة فعالة)
            // المرسل: سالب، المستقبل: موجب
            $amount = $userIsSender
                ? - ($transfer->sent_amount)
                : $transfer->received_amount;

            // نوع العملية في الجدول
            $type = $userIsSender ? 'صادرة' : 'واردة';
        }

        // إضافة العملية إلى المصفوفة لعرضها في الجدول
        $operations[] = [
            'type' => $type,
            'amount' => $amount,
            'currency' => $currency
        ];
    }
}
