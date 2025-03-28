<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ExchangeRateService;
use App\Models\SypExchangeRate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class CurrencyRates extends Component
{
    public $rates = [];
    public $sypRates = [];
    public $error = null;

    public $editingId = null;
    public $editingField = null;
    public $newValue = null;

    public $currencyNames = [
        'USD' => 'دولار',
        'TRY' => 'تركية',
        'EUR' => 'يورو',
        'GBP' => 'جنيه إسترليني',
        'SAR' => 'ريال',
        'AED' => 'درهم إماراتي',
    ];

    public function fetchCurrencyRates()
    {
        try {
            $exchangeRates = ExchangeRateService::getExchangeRates();

            $this->rates = [
                'USD/TRY' => [
                    'buy' => $exchangeRates['USD/TRY']['buy'] ?? 0,
                    'sell' => $exchangeRates['USD/TRY']['sell'] ?? 0,
                ],
                'EUR/TRY' => [
                    'buy' => $exchangeRates['EUR/TRY']['buy'] ?? 0,
                    'sell' => $exchangeRates['EUR/TRY']['sell'] ?? 0,
                ],
                'EUR/USD' => [
                    'buy' => $exchangeRates['EUR/USD']['buy'] ?? 0,
                    'sell' => $exchangeRates['EUR/USD']['sell'] ?? 0,
                ],
            ];
        } catch (\Exception $e) {
            $this->error = 'حدث خطأ أثناء جلب أسعار العملات: ' . $e->getMessage();
        }
    }

    public function fetchSypExchangeRates()
    {
        try {
            $userId = Auth::id();
            $rates = SypExchangeRate::where('user_id', $userId)
                ->get([
                    'id',
                    'exchange_rate_1',
                    'exchange_rate_2',
                    'is_active',
                    'exchange_rate_start_time',
                    'exchange_rate_end_time',
                    'currency_name_ar'
                ]);

            if ($rates->isEmpty()) {
                $defaultRates = [
                    [
                        'exchange_rate_1'  => 0,
                        'exchange_rate_2'  => 0,
                        'is_active'        => true,
                        'user_id'          => $userId,
                        'currency_name_ar' => 'الليرة السورية',
                        'currency_name_en' => 'SYP',
                        'exchange_rate_start_time' => now(),
                        'exchange_rate_end_time' => now()->addDay(),
                    ],
                ];

                foreach ($defaultRates as $rateData) {
                    SypExchangeRate::create($rateData);
                }

                $rates = SypExchangeRate::where('user_id', $userId)->get();
            }

            $this->sypRates = $rates;
        } catch (\Exception $e) {
            $this->error = 'حدث خطأ أثناء جلب أسعار صرف الليرة السورية: ' . $e->getMessage();
        }
    }

    public function toggleSypExchangeRate($id)
    {
        try {
            $rate = SypExchangeRate::findOrFail($id);
            $rate->is_active = !$rate->is_active;
            $rate->save();
            $this->fetchSypExchangeRates();
        } catch (\Exception $e) {
            $this->error = 'حدث خطأ أثناء تحديث حالة سعر الصرف: ' . $e->getMessage();
        }
    }

    public function startEditing($id, $field, $currentValue)
    {
        $this->editingId = $id;
        $this->editingField = $field;
        $this->newValue = $currentValue;
    }

    public function cancelEditing()
    {
        $this->editingId = null;
        $this->editingField = null;
        $this->newValue = null;
    }

    public function updateExchangeRate($id, $field)
    {
        $this->validate([
            'newValue' => 'required|numeric',
        ], [
            'newValue.required' => 'يجب إدخال قيمة.',
            'newValue.numeric'  => 'يجب أن تكون القيمة رقمية.',
        ]);

        try {
            $rate = SypExchangeRate::findOrFail($id);
            $rate->{$field} = $this->newValue;
            $rate->save();
            $this->fetchSypExchangeRates();
            $this->cancelEditing();
        } catch (\Exception $e) {
            $this->error = 'حدث خطأ أثناء تحديث سعر الصرف: ' . $e->getMessage();
        }
    }
    public function updateTime($id, $field)
{
    $this->validate([
        'newValue' => 'required|date_format:H:i',
    ], [
        'newValue.required' => 'يجب إدخال وقت.',
        'newValue.date_format' => 'التنسيق يجب أن يكون ساعة:دقيقة (مثال: 14:30).',
    ]);

    try {
        $rate = SypExchangeRate::findOrFail($id);
        $existingDateTime = $rate->{$field} ?? now();

        // الاحتفاظ بالتاريخ الأصلي وتحديث الوقت فقط
        $newDateTime = Carbon::parse($existingDateTime)->setTimeFromTimeString($this->newValue);

        // تحديث الحقل بالوقت الجديد مع الحفاظ على التاريخ
        $rate->{$field} = $newDateTime;

        // التحقق من أن وقت النهاية بعد وقت البداية
        if ($field === 'exchange_rate_end_time' && $newDateTime->lessThan(Carbon::parse($rate->exchange_rate_start_time))) {
            $this->error = 'وقت النهاية يجب أن يكون بعد وقت البداية.';
            return;
        }

        $rate->save();

        // تسجيل البيانات بعد التحديث
        Log::debug("Updated time: {$field} => {$newDateTime->toDateTimeString()}");

        $this->fetchSypExchangeRates();
        $this->cancelEditing();
    } catch (\Exception $e) {
        $this->error = 'حدث خطأ أثناء التحديث: ' . $e->getMessage();
    }
}


    public function mount()
    {
        $this->fetchCurrencyRates();
        $this->fetchSypExchangeRates();
    }

    public function render()
    {
        return view('livewire.currency-rates');
    }
}
