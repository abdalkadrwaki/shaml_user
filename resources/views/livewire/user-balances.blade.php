<div class="mt-6">
    <div class="flex flex-wrap justify-center gap-6">
        @foreach ($balances as $key => $balanceData)
            @php
                $currency = $balanceData['currency'];
                $balance = $balanceData['balance'];
                $balanceStatus = $balance > 0 ? 'دائن لكم' : ($balance < 0 ? 'دائن عليكم' : 'متوازن');
                $textColor = $balanceStatus === 'دائن عليكم' ? 'text-red-600' : 'text-green-600';
                $formattedBalance = number_format(abs($balance), 2);
            @endphp

            @if ($balance != 0)
                <a href="{{ route('transfers.index', [
                    'currency' => $currency->name_en,
                    'from_date' => request('from_date', now()->format('Y-m-d')),
                    'to_date' => request('to_date', now()->format('Y-m-d')),
                ]) }}"
                    class="bg-white w-64 rounded-2xl shadow-lg hover:shadow-xl transition duration-300 flex flex-col items-center text-center overflow-hidden">

                    <div class="w-full bg-blue-800 py-3">
                        <h2 class="text-lg font-semibold text-white">{{ $currency->name_ar }}</h2>
                    </div>

                    <div class="w-full bg-gray-100 py-2 border-b border-blue-800">
                        <h3 class="text-md font-medium {{ $textColor }}">{{ $balanceStatus }}</h3>
                    </div>

                    <div class="py-4">
                        <p class="text-2xl font-bold {{ $textColor }}">
                            {{ $balance < 0 ? '-' : '' }}{{ $formattedBalance }}
                        </p>
                    </div>
                </a>
            @endif
        @endforeach

        {{-- بطاقة رصيد الدولار --}}
        @if(isset($balance_in_usd_))
            @php
                $usdTextStatus = $balance_in_usd_ > 0 ? 'دائن لكم' : ($balance_in_usd_ < 0 ? 'دائن عليكم' : 'متوازن');
                $usdTextColor = $balance_in_usd_ < 0 ? 'text-red-600' : 'text-green-600';
                $formattedUSD = number_format(abs($balance_in_usd_), 2);
            @endphp

            <a href="{{ route('transfers.index', [
                'currency' => 'usd',
                'from_date' => request('from_date', now()->format('Y-m-d')),
                'to_date' => request('to_date', now()->format('Y-m-d')),
            ]) }}"
                class="bg-white w-64 rounded-2xl shadow-lg hover:shadow-xl transition duration-300 flex flex-col items-center text-center overflow-hidden">

                <div class="w-full bg-blue-800 py-3">
                    <h2 class="text-lg font-semibold text-white">ميزان</h2>
                </div>

                <div class="w-full bg-gray-100 py-2 border-b border-blue-800">
                    <h3 class="text-md font-medium {{ $usdTextColor }}">{{ $usdTextStatus }}</h3>
                </div>

                <div class="py-4">
                    <p class="text-2xl font-bold {{ $usdTextColor }}">
                        {{ $balance_in_usd_ < 0 ? '-' : '' }}{{ $formattedUSD }}
                    </p>
                </div>
            </a>
        @endif
    </div>
</div>
