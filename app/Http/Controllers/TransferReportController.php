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
        $userId = Auth::id();
        $selectedCurrency = $request->input('currency');
        $currencies = Currency::activeCurrencies();
        $currencyNames = $currencies->pluck('name_ar', 'name_en')->toArray();
        $balanceCurrencies = $selectedCurrency ? [$selectedCurrency] : [];
        $balanceMap = array_fill_keys($balanceCurrencies, 0);
        $transferData = [];

        // العمل فقط في حالة وجود فلترة للعملة أو الفترة
        if ($request->hasAny(['currency', 'from_date', 'to_date'])) {
            // تحديث الرصيد الأولي قبل فترة البحث
            if ($request->filled('from_date') && $selectedCurrency) {
                try {
                    $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
                    $initialTransactions = Transfer::where(function ($query) use ($userId) {
                            $query->where('user_id', $userId)
                                  ->orWhere('destination', $userId);
                        })
                        ->where('created_at', '<', $fromDate)
                        ->where(function ($query) {
                            $query->whereIn('transaction_type', ['Transfer', 'Exchange'])
                                  ->orWhere(function ($q) {
                                      $q->where('transaction_type', 'Credit')
                                        ->where('status', 'Delivered');
                                  });
                        })
                        ->where(function ($query) use ($selectedCurrency) {
                            $query->where('sent_currency', $selectedCurrency)
                                  ->orWhere('received_currency', $selectedCurrency);
                        })
                        ->orderBy('created_at')
                        ->get();

                    foreach ($initialTransactions as $transaction) {
                        $this->updateBalanceMap($balanceMap, $transaction, $selectedCurrency, $userId);
                    }
                } catch (\Exception $e) {
                    // يمكن هنا تسجيل الأخطاء إذا لزم الأمر
                }
            }

            // حفظ رصيد أول المدة
            $initialBalance = $balanceMap[$selectedCurrency] ?? 0;

            // بناء استعلام المعاملات ضمن الفترة المطلوبة
            $query = Transfer::query()
                ->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhere('destination', $userId);
                })
                ->where(function ($q) {
                    $q->whereIn('transaction_type', ['Transfer', 'Exchange'])
                      ->orWhere(function ($q2) {
                          $q2->where('transaction_type', 'Credit')
                             ->where('status', 'Delivered');
                      });
                });

            if ($selectedCurrency) {
                $query->where(function ($q) use ($selectedCurrency) {
                    $q->where('sent_currency', $selectedCurrency)
                      ->orWhere('received_currency', $selectedCurrency);
                });
            }

            if ($request->filled('from_date') && $request->filled('to_date')) {
                try {
                    $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
                    $toDate = Carbon::parse($request->input('to_date'))->endOfDay();
                    $query->whereBetween('created_at', [$fromDate, $toDate]);
                } catch (\Exception $e) {
                    // تسجيل الأخطاء إن لزم الأمر
                }
            }

            $transfers = $query->orderBy('created_at')->get();

            // معالجة كل معاملة وإضافتها للبيانات التفصيلية
            foreach ($transfers as $transfer) {
                $this->processTransfer($transfer, $balanceMap, $transferData, $selectedCurrency, $userId);
            }

            // الرصيد النهائي بعد المعاملات
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

    /**
     * تحديث خريطة الرصيد بحسب نوع المعاملة والعملات المحددة.
     */
    private function updateBalanceMap(array &$balanceMap, Transfer $transfer, ?string $currencyFilter, int $userId): void
    {
        // تعيين قائمة العملات المراد تحديثها
        $currenciesToUpdate = $currencyFilter ? [$currencyFilter] : array_keys($balanceMap);

        switch ($transfer->transaction_type) {
            case 'Transfer':
                $this->handleTransfer($balanceMap, $transfer, $currenciesToUpdate, $userId);
                break;
            case 'Exchange':
                if ($transfer->status === 'Delivered') {
                    $this->handleExchange($balanceMap, $transfer, $currenciesToUpdate, $userId);
                }
                break;
            case 'Credit':
                if ($transfer->status === 'Delivered') {
                    $this->handleCredit($balanceMap, $transfer, $currenciesToUpdate);
                }
                break;
        }
    }

    /**
     * معالجة معاملات من نوع Transfer.
     */
    private function handleTransfer(array &$balanceMap, Transfer $transfer, array $currenciesToUpdate, int $userId): void
    {
        $senderCurrency = $transfer->sent_currency;
        $receiverCurrency = $transfer->received_currency;

        if ($transfer->status === 'Archived') {
            if ($transfer->user_id == $userId && in_array($senderCurrency, $currenciesToUpdate)) {
                $balanceMap[$senderCurrency] += ($transfer->sent_amount + $transfer->fees);
            }
            if ($transfer->destination == $userId && in_array($receiverCurrency, $currenciesToUpdate)) {
                $balanceMap[$receiverCurrency] -= ($transfer->received_amount + $transfer->fees);
            }
        } else {
            if ($transfer->user_id == $userId && in_array($senderCurrency, $currenciesToUpdate)) {
                $balanceMap[$senderCurrency] -= ($transfer->sent_amount + $transfer->fees);
            }
            if ($transfer->destination == $userId && in_array($receiverCurrency, $currenciesToUpdate)) {
                $balanceMap[$receiverCurrency] += ($transfer->received_amount + $transfer->fees);
            }
        }
    }

    /**
     * معالجة معاملات من نوع Exchange.
     */
    private function handleExchange(array &$balanceMap, Transfer $transfer, array $currenciesToUpdate, int $userId): void
    {
        $userIsSender = ($transfer->user_id == $userId);

        if ($userIsSender) {
            if (in_array($transfer->sent_currency, $currenciesToUpdate)) {
                $balanceMap[$transfer->sent_currency] -= ($transfer->sent_amount + $transfer->fees);
            }
            if (in_array($transfer->received_currency, $currenciesToUpdate)) {
                $balanceMap[$transfer->received_currency] += $transfer->received_amount;
            }
        } else {
            if (in_array($transfer->sent_currency, $currenciesToUpdate)) {
                $balanceMap[$transfer->sent_currency] += $transfer->sent_amount;
            }
            if (in_array($transfer->received_currency, $currenciesToUpdate)) {
                $balanceMap[$transfer->received_currency] -= ($transfer->received_amount + $transfer->fees);
            }
        }
    }

    /**
     * معالجة معاملات من نوع Credit.
     */
    private function handleCredit(array &$balanceMap, Transfer $transfer, array $currenciesToUpdate): void
    {
        if (in_array($transfer->sent_currency, $currenciesToUpdate)) {
            $balanceMap[$transfer->sent_currency] += $transfer->sent_amount;
        }
    }

    /**
     * معالجة معاملة واحدة وإضافة تفاصيل العملية مع تحديث الرصيد.
     */
    private function processTransfer(Transfer $transfer, array &$balanceMap, array &$transferData, ?string $selectedCurrency, int $userId): void
    {
        $prevBalance = $selectedCurrency ? ($balanceMap[$selectedCurrency] ?? 0) : 0;
        $this->updateBalanceMap($balanceMap, $transfer, $selectedCurrency, $userId);
        $currentBalance = $selectedCurrency ? ($balanceMap[$selectedCurrency] ?? 0) : 0;

        $this->buildTransferRow($transfer, $transferData, $selectedCurrency, $prevBalance, $currentBalance, $userId);
    }

    /**
     * بناء صفوف البيانات التفصيلية لكل معاملة.
     */
    private function buildTransferRow(Transfer $transfer, array &$transferData, ?string $currencyFilter, float $prevBalance, float $currentBalance, int $userId): void
    {
        $operations = [];

        if ($transfer->transaction_type === 'Exchange' && $transfer->status === 'Delivered') {
            $this->addExchangeOperations($transfer, $operations, $currencyFilter, $userId);
        } elseif ($transfer->transaction_type === 'Credit' && $transfer->status === 'Delivered') {
            $this->addCreditOperation($transfer, $operations, $currencyFilter);
        } else {
            $this->addTransferOperation($transfer, $operations, $currencyFilter, $userId);
        }

        foreach ($operations as $operation) {
            $transferData[] = [
                'transfer'           => $transfer,
                'operation'          => $operation['type'],
                'amount'             => $operation['amount'],
                'currency'           => $operation['currency'],
                'cumulative_balance' => $currentBalance,
            ];
        }
    }

    /**
     * إضافة عمليات تبادل العملات.
     */
    private function addExchangeOperations(Transfer $transfer, array &$operations, ?string $currencyFilter, int $userId): void
    {
        $userIsSender = ($transfer->user_id == $userId);

        if (!$currencyFilter || $currencyFilter === $transfer->sent_currency) {
            $operations[] = [
                'type'     => $userIsSender ? 'sell' : 'buy',
                'amount'   => $userIsSender ? -($transfer->sent_amount + $transfer->fees) : $transfer->sent_amount,
                'currency' => $transfer->sent_currency,
            ];
        }

        if (!$currencyFilter || $currencyFilter === $transfer->received_currency) {
            $operations[] = [
                'type'     => $userIsSender ? 'buy' : 'sell',
                'amount'   => $userIsSender ? $transfer->received_amount : -($transfer->received_amount + $transfer->fees),
                'currency' => $transfer->received_currency,
            ];
        }
    }

    /**
     * إضافة عملية الإيداع.
     */
    private function addCreditOperation(Transfer $transfer, array &$operations, ?string $currencyFilter): void
    {
        if (!$currencyFilter || $currencyFilter === $transfer->sent_currency) {
            $operations[] = [
                'type'     => 'credit',
                'amount'   => $transfer->sent_amount,
                'currency' => $transfer->sent_currency,
            ];
        }
    }

    /**
     * إضافة عملية التحويل العادية.
     */
    private function addTransferOperation(Transfer $transfer, array &$operations, ?string $currencyFilter, int $userId): void
    {
        $userIsSender = ($transfer->user_id == $userId);
        $currency = $userIsSender ? $transfer->sent_currency : $transfer->received_currency;

        if ($currencyFilter && $currency !== $currencyFilter) {
            return;
        }

        if ($transfer->status === 'Archived') {
            $amount = $userIsSender ? $transfer->sent_amount : -$transfer->received_amount;
            $type = $userIsSender ? 'ملغاة (مستردة)' : 'ملغاة (مخصومة)';
        } else {
            $amount = $userIsSender ? -$transfer->sent_amount : $transfer->received_amount;
            $type = $userIsSender ? 'صادرة' : 'واردة';
        }

        $operations[] = [
            'type'     => $type,
            'amount'   => $amount,
            'currency' => $currency,
        ];
    }
}
