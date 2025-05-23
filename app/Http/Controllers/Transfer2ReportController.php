<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Currency;

class Transfer2ReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedCurrency = $request->currency;
        try {
            $clientId = decrypt($request->clientId);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // مثلاً: رجّع خطأ أو صفحة 403
            abort(403, 'رابط غير صالح');
        }

        $currencies = Currency::activeCurrencies();
        $currencyNames = $currencies->pluck('name_ar', 'name_en')->toArray();
        // تهيئة خريطة الأرصدة لعملة المحددة فقط
        $balanceCurrencies = $selectedCurrency ? [$selectedCurrency] : [];
        $balanceMap = array_fill_keys($balanceCurrencies, 0);
        $transferData = [];

        if ($request->hasAny(['currency', 'from_date', 'to_date'])) {

            // حساب الرصيد الابتدائي للعملة المحددة (المعاملات التي تسبق تاريخ البحث)
            if ($request->filled('from_date') && $selectedCurrency) {
                try {
                    $fromDate = Carbon::parse($request->from_date)->startOfDay();

                    $initialQuery = Transfer::where(function ($q) use ($clientId) {
                        $q->where(function ($q2) use ($clientId) {
                            $q2->where('user_id', Auth::id())
                                ->where('destination', $clientId);
                        })
                        ->orWhere(function ($q3) use ($clientId) {
                            $q3->where('user_id', $clientId)
                                ->where('destination', Auth::id());
                        });
                    })
                    ->where('created_at', '<', $fromDate)
                    ->where(function ($q) {
                        $q->whereIn('transaction_type', ['Transfer', 'Exchange'])
                          ->orWhere(function ($q2) {
                              $q2->where('transaction_type', 'Credit')
                                 ->where('status', 'Delivered');
                          });
                    })
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

            // استعلام المعاملات ضمن الفترة المطلوبة باستخدام نفس شروط التصفية
            $query = Transfer::where(function ($q) use ($clientId) {
                $q->where(function ($q2) use ($clientId) {
                    $q2->where('user_id', Auth::id())
                        ->where('destination', $clientId);
                })
                ->orWhere(function ($q3) use ($clientId) {
                    $q3->where('user_id', $clientId)
                        ->where('destination', Auth::id());
                });
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

        return view('transfers.Transfer2Report', compact(
            'transferData',
            'currencies',
            'currencyNames',
            'selectedCurrency',
            'initialBalance',
            'finalBalance',
            'clientId'
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
        $userIsSender = ($transfer->user_id == Auth::id());

        if ($transfer->status === 'Delivered') {
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
        $userIsSender = ($transfer->user_id == Auth::id());

        if (!$currencyFilter || $currencyFilter === $transfer->sent_currency) {
            $operations[] = [
                'type'   => $userIsSender ? 'sell' : 'buy',
                'amount' => $userIsSender
                    ? - ($transfer->sent_amount + $transfer->fees)
                    :  $transfer->sent_amount,
                'currency' => $transfer->sent_currency,
            ];
        }

        if (!$currencyFilter || $currencyFilter === $transfer->received_currency) {
            $operations[] = [
                'type'   => $userIsSender ? 'buy' : 'sell',
                'amount' => $userIsSender
                    ?  $transfer->received_amount
                    : - ($transfer->received_amount + $transfer->fees),
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
        $userIsSender = ($transfer->user_id == Auth::id());
        $currency = $userIsSender ? $transfer->sent_currency : $transfer->received_currency;

        if ($currencyFilter && $currency !== $currencyFilter) {
            return;
        }

        if ($transfer->status === 'Archived') {
            $amount = $userIsSender
                ? $transfer->sent_amount
                : - ($transfer->received_amount);
            $type = $userIsSender ? 'ملغاة (مستردة)' : 'ملغاة (مخصومة)';
        } else {
            $amount = $userIsSender
                ? - ($transfer->sent_amount)
                : $transfer->received_amount;
            $type = $userIsSender ? 'صادرة' : 'واردة';
        }

        $operations[] = [
            'type' => $type,
            'amount' => $amount,
            'currency' => $currency
        ];
    }
}
