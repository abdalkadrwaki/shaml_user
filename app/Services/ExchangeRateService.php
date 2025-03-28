<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ExchangeRate;
use Exception;

class ExchangeRateService
{
    // مفتاح الكاش ومدة التخزين (3600 ثانية = ساعة واحدة)
    const CACHE_KEY = 'exchange_rates';
    const CACHE_DURATION = 3600;

    // لتخزين أسعار الصرف داخل نفس الطلب لتقليل عمليات الوصول لنظام الكاش
    private static $rates = null;

    /**
     * الحصول على أسعار الصرف مع التحقق أولاً من وجود بيانات مخزنة بالكاش.
     * يتم جلب البيانات أولاً من الـ API، وإذا لم تتوفر بيانات أو بعض العملات غير موجودة،
     * يتم الرجوع إلى قاعدة البيانات.
     *
     * @return array
     */
    public static function getExchangeRates()
    {
        // استخدام المتغير الثابت لتقليل الوصول لنظام الكاش داخل نفس الطلب
        if (self::$rates !== null) {
            return self::$rates;
        }

        // التحقق أولاً من وجود البيانات في الكاش
        $cachedRates = Cache::get(self::CACHE_KEY);
        if ($cachedRates) {
            self::$rates = $cachedRates;
            return self::$rates;
        }

        $apiRates = [];
        // محاولة جلب البيانات من الـ API
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'X-Requested-With' => 'XMLHttpRequest',
                ])
                ->get('https://www.haremaltin.com/ajax/all_prices');

            if ($response->successful()) {
                $data = $response->json();
                $apiRates = self::formatRatesFromAPI($data);
            } else {
                Log::error("API response unsuccessful: " . $response->status());
            }
        } catch (Exception $e) {
            Log::error('Failed to fetch exchange rates from API: ' . $e->getMessage());
        }

        // جلب أسعار الصرف من قاعدة البيانات كخيار بديل أو لاستكمال البيانات المفقودة
        try {
            $exchangeRates = ExchangeRate::all();
            $dbRates = self::formatRates($exchangeRates);
        } catch (Exception $e) {
            Log::error('Failed to fetch exchange rates from database: ' . $e->getMessage());
            $dbRates = [];
        }

        // دمج البيانات بحيث إذا كانت بيانات الـ API متوفرة وصحيحة نستخدمها، وإلا نستخدم بيانات قاعدة البيانات
        $mergedRates = [];
        // تحديد مجموعة المفاتيح المتوقعة (من الـ API والقاعدة معاً)
        $expectedPairs = array_unique(array_merge(array_keys($dbRates), array_keys($apiRates)));

        foreach ($expectedPairs as $pair) {
            // إذا كانت بيانات الـ API موجودة ولها قيم شراء وبيع صحيحة (> 0)
            if (isset($apiRates[$pair]) && $apiRates[$pair]['buy'] > 0 && $apiRates[$pair]['sell'] > 0) {
                $mergedRates[$pair] = $apiRates[$pair];
            } elseif (isset($dbRates[$pair])) {
                // استخدام بيانات قاعدة البيانات في حال عدم توفر بيانات API صحيحة
                $mergedRates[$pair] = $dbRates[$pair];
            }
        }

        if (empty($mergedRates)) {
            Log::emergency('No exchange rates available!');
            $mergedRates = self::getDefaultRates();
        }

        // تخزين البيانات المدمجة في الكاش
        Cache::put(self::CACHE_KEY, $mergedRates, self::CACHE_DURATION);
        self::$rates = $mergedRates;
        return self::$rates;
    }

    /**
     * تنسيق البيانات المسترجعة من قاعدة البيانات لتتناسب مع التطبيق.
     * لا يتم تغيير اسم الدالة لتفادي تأثيره على الصفحات الأخرى.
     *
     * @param \Illuminate\Database\Eloquent\Collection $exchangeRates
     * @return array
     */
    private static function formatRates($exchangeRates)
    {
        $formattedRates = [];

        foreach ($exchangeRates as $rate) {
            $formattedRates[$rate->currency_pair] = [
                'buy'  => $rate->buy_rate,
                'sell' => $rate->sell_rate,
            ];
        }

        return $formattedRates;
    }

    /**
     * تنسيق بيانات الـ API لتتناسب مع التطبيق.
     *
     * @param array $data البيانات المسترجعة من الـ API
     * @return array
     */
    private static function formatRatesFromAPI($data)
    {
        return [
            'USD/TRY' => [
                'buy'  => $data['data']['USDTRY']['alis'] ?? 0,
                'sell' => $data['data']['USDTRY']['satis'] ?? 0,
            ],
            'EUR/TRY' => [
                'buy'  => $data['data']['EURTRY']['alis'] ?? 0,
                'sell' => $data['data']['EURTRY']['satis'] ?? 0,
            ],
            'EUR/USD' => [
                'buy'  => $data['data']['EURUSD']['alis'] ?? 0,
                'sell' => $data['data']['EURUSD']['satis'] ?? 0,
            ],
            'SAR/TRY' => [
                'buy'  => $data['data']['SARTRY']['alis'] ?? 0,
                'sell' => $data['data']['SARTRY']['satis'] ?? 0,
            ],
            // إضافة زوج العملة لحساب الليرة السورية مباشرة على الدولار
            'USD/SYP' => [
                'buy'  => $data['data']['USDSYP']['alis'] ?? 0,
                'sell' => $data['data']['USDSYP']['satis'] ?? 0,
            ],
        ];
    }

    /**
     * إرجاع قيم افتراضية في حال عدم توفر بيانات حية.
     *
     * @return array
     */
    private static function getDefaultRates()
    {
        return [
            'USD/TRY' => ['buy' => 32.0, 'sell' => 32.5],
            'EUR/TRY' => ['buy' => 35.0, 'sell' => 35.5],
            'EUR/USD' => ['buy' => 1.08, 'sell' => 1.09],
            'SAR/TRY' => ['buy' => 9.0, 'sell' => 9.2],
            // قيمة افتراضية لتحويل الليرة السورية مباشرة إلى الدولار
            'USD/SYP' => ['buy' => 2500, 'sell' => 2550],
        ];
    }

    /**
     * تحويل المبلغ إلى الدولار الأمريكي بناءً على العملة وسعر الصرف.
     *
     * @param string $currency نوع العملة (USD, TRY, EUR, SAR, SYP)
     * @param float $amount المبلغ المراد تحويله
     * @param array $exchangeRates أسعار الصرف الحالية
     * @return float
     * @throws Exception في حال حدوث خطأ أثناء التحويل
     */
    public static function convertToUSD($currency, $amount, $exchangeRates)
    {
        if ($currency === 'USD') {
            return $amount;
        }

        try {
            switch ($currency) {
                case 'TRY':
                    $usdTry = $exchangeRates['USD/TRY']['buy'] ?? 0;
                    if ($usdTry <= 0) {
                        $defaultRates = self::getDefaultRates();
                        $usdTry = $defaultRates['USD/TRY']['buy'] ?? 0;
                    }
                    if ($usdTry <= 0) {
                        throw new Exception('Invalid USD/TRY rate');
                    }
                    return $amount / $usdTry;

                case 'EUR':
                    $eurUsd = $exchangeRates['EUR/USD']['buy'] ?? 0;
                    if ($eurUsd <= 0) {
                        $defaultRates = self::getDefaultRates();
                        $eurUsd = $defaultRates['EUR/USD']['buy'] ?? 0;
                    }
                    if ($eurUsd <= 0) {
                        throw new Exception('Invalid EUR/USD rate');
                    }
                    return $amount * $eurUsd;

                case 'SAR':
                    $directRate = $exchangeRates['USD/SAR']['buy'] ?? 0;
                    if ($directRate > 0) {
                        return $amount / $directRate;
                    }
                    $defaultRates = self::getDefaultRates();
                    $sarTry = $exchangeRates['SAR/TRY']['buy'] ?? 0;
                    if ($sarTry <= 0) {
                        $sarTry = $defaultRates['SAR/TRY']['buy'] ?? 0;
                    }
                    $usdTry = $exchangeRates['USD/TRY']['buy'] ?? 0;
                    if ($usdTry <= 0) {
                        $usdTry = $defaultRates['USD/TRY']['buy'] ?? 0;
                    }
                    if ($sarTry > 0 && $usdTry > 0) {
                        $amountInTRY = $amount / $sarTry;
                        return $amountInTRY / $usdTry;
                    }
                    throw new Exception('Invalid rate for SAR conversion');

                case 'SYP':
                    // حساب مباشر من الليرة السورية إلى الدولار باستخدام سعر USD/SYP
                    $directRate = $exchangeRates['USD/SYP']['buy'] ?? 0;
                    if ($directRate > 0) {
                        return $amount / $directRate;
                    }
                    throw new Exception('Direct USD/SYP rate unavailable for conversion');

                default:
                    throw new Exception('Unsupported currency: ' . $currency);
            }
        } catch (Exception $e) {
            Log::error("Conversion error: " . $e->getMessage());
            throw new Exception("Currency conversion unavailable temporarily");
        }
    }
}
