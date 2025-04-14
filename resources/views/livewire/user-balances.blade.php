<div class="px-4 md:px-6 lg:px-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6 mt-6">
        @foreach ($balances as $key => $balanceData)
            @php
                $currency = $balanceData['currency'];
                $balance = $balanceData['balance'];
                $balanceStatus = $balance > 0 ? 'دائن لكم' : ($balance < 0 ? 'دائن عليكم' : '');
                $textColor = $balanceStatus === 'دائن عليكم' ? 'text-red-600' : 'text-green-600';
                $formattedBalance = number_format(abs($balance), 2);
            @endphp

            @if ($balance != 0)
                <a href="{{ route('transfers.index', [
                    'currency' => $currency->name_en,
                    'from_date' => request('from_date', now()->format('Y-m-d')),
                    'to_date' => request('to_date', now()->format('Y-m-d')),
                ]) }}"
                    class="group bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden transform hover:-translate-y-1">
                    <div class="bg-gradient-to-r from-blue-800 to-blue-600 p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-white">{{ $currency->name_ar }}</h3>
                            <span class="text-white opacity-90">{{ $currency->symbol }}</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="mb-4">
                            <p class="text-gray-500 text-sm">الحالة</p>
                            <p class="text-lg font-medium {{ $textColor }}">{{ $balanceStatus }}</p>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <p class="text-gray-500 text-sm">القيمة</p>
                            <p class="text-2xl font-bold {{ $textColor }}">
                                {{ $balance < 0 ? '-' : '' }}{{ $formattedBalance }}
                            </p>
                        </div>
                    </div>
                </a>
            @endif
        @endforeach

        {{-- بطاقة رصيد الدولار --}}
        @if(isset($balance_in_usd_))
            @php
                $usdTextStatus = $balance_in_usd_ > 0 ? 'دائن لكم' : ($balance_in_usd_ < 0 ? 'دائن عليكم' : '');
                $usdTextColor = $balance_in_usd_ < 0 ? 'text-red-600' : 'text-green-600';
                $formattedUSD = number_format(abs($balance_in_usd_), 2);
            @endphp
            <a href="{{ route('transfers.index', [
                'currency' => 'usd',
                'from_date' => request('from_date', now()->format('Y-m-d')),
                'to_date' => request('to_date', now()->format('Y-m-d')),
            ]) }}"
                class="group bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden transform hover:-translate-y-1">
                <div class="bg-gradient-to-r from-blue-800 to-blue-600 p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-white">ميزان الدولار</h3>
                        <span class="text-white opacity-90">$</span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-gray-500 text-sm">الحالة</p>
                        <p class="text-lg font-medium {{ $usdTextColor }}">{{ $usdTextStatus }}</p>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-gray-500 text-sm">القيمة</p>
                        <p class="text-2xl font-bold {{ $usdTextColor }}">
                            {{ $balance_in_usd_ < 0 ? '-' : '' }}{{ $formattedUSD }}
                        </p>
                    </div>
                </div>
            </a>
        @endif
    </div>
</div>
