<div>
    <div class="flex gap-4 justify-between mt-4">
        @foreach ($balances as $key => $balanceData)
            @php
                $currency = $balanceData['currency'];
                $balance = $balanceData['balance'];
                $balanceStatus = $balance > 0 ? 'دائن لكم' : ($balance < 0 ? 'دائن عليكم' : '');
                $textColor = $balanceStatus === 'دائن عليكم' ? 'text-red-500' : 'text-green-500';
                $formattedBalance = number_format(abs($balance), 2);
            @endphp

            @if ($balance != 0)
                <a href="{{ route('transfers.index', [
                    'currency' => $currency->name_en,
                    'from_date' => request('from_date', now()->format('Y-m-d')),
                    'to_date' => request('to_date', now()->format('Y-m-d')),
                ]) }}"
                    class="bg-white shadow-md rounded-md flex flex-col items-center text-center flex-1 mx-2 no-underline hover:no-underline">
                    <div class="w-full bg-blue-900 py-2 c rounded-t-md">
                        <h2 class="text-xl font-bold text-white">{{ $currency->name_ar }}</h2>
                    </div>
                    <div class="w-full bg-custom-gray2 py-2 rounded-t-md border-b border-blue-900">
                        <h2 class="text-xl font-bold {{ $textColor }}">{{ $balanceStatus }}</h2>
                    </div>
                    <div class="w-auto p-1 m-2 rounded-md">
                        <p class="text-2xl mt-2 {{ $textColor }}">
                            @if ($balance < 0)
                                -{{ $formattedBalance }}
                            @else
                                {{ $formattedBalance }}
                            @endif
                        </p>
                    </div>
                </a>
            @endif
        @endforeach

        {{-- بطاقة رصيد الدولار --}}
        @if(isset($balance_in_usd_))
            @php
                $usdTextStatus = $balance_in_usd_ > 0 ? 'دائن لكم' : ($balance_in_usd_ < 0 ? 'دائن عليكم' : '');
                $usdTextColor = $balance_in_usd_ < 0 ? 'text-red-500' : 'text-green-500';
                $formattedUSD = number_format(abs($balance_in_usd_), 2);
            @endphp
            <a href="{{ route('transfers.index', [
                'currency' => 'usd',
                'from_date' => request('from_date', now()->format('Y-m-d')),
                'to_date' => request('to_date', now()->format('Y-m-d')),
            ]) }}"
                class="bg-white shadow-md rounded-md flex flex-col items-center text-center flex-1 mx-2 no-underline hover:no-underline">
                <div class="w-full bg-blue-900 py-2 rounded-t-md">
                    <h2 class="text-xl font-bold text-white">ميزان</h2>
                </div>
                <div class="w-full bg-custom-gray2 py-2 rounded-t-md border-b border-blue-900">
                    <h2 class="text-xl font-bold {{ $usdTextColor }}">{{ $usdTextStatus }}</h2>
                </div>
                <div class="w-auto p-1 m-2 rounded-md">
                    <p class="text-2xl mt-2 {{ $usdTextColor }}">
                        @if ($balance_in_usd_ < 0)
                            -{{ $formattedUSD }}
                        @else
                            {{ $formattedUSD }}
                        @endif
                    </p>
                </div>
            </a>
        @endif
    </div>

</div>
