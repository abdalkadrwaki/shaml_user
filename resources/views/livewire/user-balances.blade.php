<div class="px-4 py-6">
    <div class="flex flex-nowrap overflow-x-auto pb-4 gap-4 scrollbar-hide"> <!-- إخفاء شريط التمرير -->
        @foreach ($balances as $key => $balanceData)
            @php
                // ... نفس الكود البرمجي السابق ...
            @endphp

            @if ($balance != 0)
                <a href="{{ route('transfers.index', [/* ... */]) }}"
                    class="min-w-[300px] flex-shrink-0 bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border border-gray-100">
                    <div class="flex justify-between items-center p-4 bg-blue-50 border-b-2 border-blue-200">
                        <div>
                            <h3 class="text-xl font-bold text-blue-800">{{ $currency->name_ar }}</h3>
                            <p class="text-sm text-blue-600">{{ $currency->name_en }}</p>
                        </div>
                        <span class="text-2xl text-blue-700">{{ $currency->symbol }}</span>
                    </div>

                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">الحالة:</span>
                            <span class="px-3 py-1 rounded-full {{ $textColor }} bg-opacity-20 {{ $balanceStatus === 'دائن عليكم' ? 'bg-red-100' : 'bg-green-100' }}">
                                {{ $balanceStatus }}
                            </span>
                        </div>

                        <div class="text-center py-3">
                            <p class="text-4xl font-bold {{ $textColor }}">
                                {{ $balance < 0 ? '-' : '' }}{{ $formattedBalance }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">رصيد {{ $currency->name_ar }}</p>
                        </div>
                    </div>
                </a>
            @endif
        @endforeach

        {{-- بطاقة الدولار --}}
        @if(isset($balance_in_usd_))
            @php
                // ... نفس الكود البرمجي السابق ...
            @endphp
            <a href="{{ route('transfers.index', [/* ... */]) }}"
                class="min-w-[300px] flex-shrink-0 bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border border-gray-100">
                <div class="flex justify-between items-center p-4 bg-green-50 border-b-2 border-green-200">
                    <div>
                        <h3 class="text-xl font-bold text-green-800">رصيد الدولار</h3>
                        <p class="text-sm text-green-600">USD Balance</p>
                    </div>
                    <span class="text-2xl text-green-700">$</span>
                </div>

                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">الحالة:</span>
                        <span class="px-3 py-1 rounded-full {{ $usdTextColor }} bg-opacity-20 {{ $usdTextStatus === 'دائن عليكم' ? 'bg-red-100' : 'bg-green-100' }}">
                            {{ $usdTextStatus }}
                        </span>
                    </div>

                    <div class="text-center py-3">
                        <p class="text-4xl font-bold {{ $usdTextColor }}">
                            {{ $balance_in_usd_ < 0 ? '-' : '' }}{{ $formattedUSD }}
                        </p>
                        <p class="text-sm text-gray-500 mt-1">رصيد بالدولار الأمريكي</p>
                    </div>
                </div>
            </a>
        @endif
    </div>
</div>
