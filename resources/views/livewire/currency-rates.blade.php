<div class="container mt-4 w-1/2 ">
    <div class="card bg-light shadow-sm ">
        <div class="card-body">
            <div class="w-full bg-blue-900 py-2 rounded-t-md mt-1">
                <h2 class="text-xl text-center font-bold text-white">اسعار الصرف</h2>
            </div>

            @if ($error)
                <div class="alert alert-danger text-center">
                    {{ $error }}
                </div>
            @else

                    <div class="table-responsive">
                        <table class="w-full  table table-bordered table-hover table-striped text-center"
                            style="direction: rtl;">
                            <thead class="bg-blue-900">
                                <tr>
                                    <th class="py-3 px-4  text-center">العملة</th>
                                    <th class="py-3 px-4  text-center">سعر الشراء</th>
                                    <th class="py-3 px-4  text-center">سعر البيع</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rates as $currency => $rate)
                                    @php
                                        $currencyPair = explode('/', $currency);
                                        $currencyFrom = $currencyPair[0];
                                        $currencyTo = $currencyPair[1];
                                        $currencyFromName = $currencyNames[$currencyFrom] ?? $currencyFrom;
                                        $currencyToName = $currencyNames[$currencyTo] ?? $currencyTo;
                                    @endphp
                                    <tr>
                                        <td class="py-2 px-4 border-b">{{ $currencyFromName }} / {{ $currencyToName }}</td>
                                        <td class="py-2 px-4 border-b">{{ number_format($rate['buy'], 2) }}</td>
                                        <td class="py-2 px-4 border-b">{{ number_format($rate['sell'], 2) }}</td>

                                    </tr>
                                @endforeach



                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive">
                        <table class=" table table-bordered table-hover table-striped text-center"
                            style="direction: rtl;">
                            <thead class="bg-blue-900">
                                <tr>
                                    <th colspan="4"  class="py-3 px-4  text-center bg-red-400">إدارة السوري</th>


                                </tr>
                            </thead>
                            <tbody>


                                @foreach ($sypRates as $rate)
                                    <tr>
                                        <td class=" text-blue-600 font-bold">سعر قص ليرة سوية </td>
                                        <td class="py-2 px-4 border-b">
                                            <button
                                                onclick="if (confirm('هل أنت متأكد من {{ $rate->is_active ? 'إيقاف' : 'تفعيل' }} الصرف؟')) { @this.call('toggleSypExchangeRate', {{ $rate->id }}) }"
                                                class="px-4 py-1 rounded text-white text-sm font-bold transition duration-200 ease-in-out
                                                    {{ $rate->is_active ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}">
                                                {{ $rate->is_active ? 'مفعل' : 'غير مفعل' }}
                                            </button>
                                        </td>



                                        <td class="py-2 px-4 border-b">
                                            @if ($editingId === $rate->id && $editingField === 'exchange_rate_1')
                                                <input type="text" wire:model.defer="newValue"
                                                    class="form-control d-inline-block w-auto">
                                                <button
                                                    wire:click="updateExchangeRate({{ $rate->id }}, 'exchange_rate_1')"
                                                    class="btn btn-primary btn-sm">حفظ</button>
                                                <button wire:click="cancelEditing"
                                                    class="btn btn-secondary btn-sm">إلغاء</button>
                                            @else
                                                <button
                                                    wire:click="startEditing({{ $rate->id }}, 'exchange_rate_1', {{ $rate->exchange_rate_1 }})"
                                                    class="btn">
                                                    {{ number_format($rate->exchange_rate_1, 0) }}
                                                </button>
                                            @endif
                                        </td>


                                    </tr>
                                @endforeach
                                @foreach ($sypRates as $rate)
                                    <tr>

                                        <td class=" text-blue-600 font-bold  bg-gray-500">تحديد وقت عمل</td>

                                        <td class="py-2 px-4 border-b">
                                            <span class=" text-blue-600 font-bold">وقت البدء </span>
                                            @if ($editingId === $rate->id && $editingField === 'exchange_rate_start_time')
                                                <input type="time" step="60" wire:model.defer="newValue"
                                                    class="form-control d-inline-block w-auto">
                                                <button
                                                    wire:click="updateTime({{ $rate->id }}, 'exchange_rate_start_time')"
                                                    class="btn btn-primary btn-sm">حفظ</button>
                                                <button wire:click="cancelEditing"
                                                    class="btn btn-secondary btn-sm">إلغاء</button>
                                            @else
                                                <button
                                                    wire:click="startEditing({{ $rate->id }}, 'exchange_rate_start_time', '{{ $rate->exchange_rate_start_time }}')"
                                                    class="btn">
                                                    {{ $rate->exchange_rate_start_time ? \Carbon\Carbon::parse($rate->exchange_rate_start_time)->format('H:i') : '--' }}
                                                </button>
                                            @endif
                                        </td>

                                        <!-- وقت الانتهاء -->
                                        <td class="py-2 px-4 border-b">
                                            <span class="  text-blue-600 font-bold">وقت الانتهاء </span>
                                            @if ($editingId === $rate->id && $editingField === 'exchange_rate_end_time')
                                                <input type="time" step="60" wire:model.defer="newValue"
                                                    class="form-control d-inline-block w-auto">
                                                <button
                                                    wire:click="updateTime({{ $rate->id }}, 'exchange_rate_end_time')"
                                                    class="btn btn-primary btn-sm">حفظ</button>
                                                <button wire:click="cancelEditing"
                                                    class="btn btn-secondary btn-sm">إلغاء</button>
                                            @else
                                                <button
                                                    wire:click="startEditing({{ $rate->id }}, 'exchange_rate_end_time', '{{ $rate->exchange_rate_end_time }}')"
                                                    class="btn">
                                                    {{ $rate->exchange_rate_end_time ? \Carbon\Carbon::parse($rate->exchange_rate_end_time)->format('H:i') : '--' }}
                                                </button>

                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

            @endif
        </div>
    </div>
</div>
