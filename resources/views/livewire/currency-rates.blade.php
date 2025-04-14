<div class="container mt-6 max-w-5xl mx-auto">
    <div class="card bg-white shadow-lg rounded-lg p-4">
        <div class="bg-blue-900 py-2 rounded-md mb-4">
            <h2 class="text-xl font-bold text-white text-center">أسعار الصرف</h2>
        </div>

        @if ($error)
            <div class="alert alert-danger text-center">
                {{ $error }}
            </div>
        @else
            {{-- جدول أسعار الصرف --}}
            <div class="table-responsive mb-6">
                <table class="w-full table table-bordered table-hover text-center" style="direction: rtl;">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="py-3">العملة</th>
                            <th class="py-3">سعر الشراء</th>
                            <th class="py-3">سعر البيع</th>
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
                                <td class="py-2">{{ $currencyFromName }} / {{ $currencyToName }}</td>
                                <td class="py-2">{{ number_format($rate['buy'], 2) }}</td>
                                <td class="py-2">{{ number_format($rate['sell'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- إدارة الليرة السورية --}}
            <div class="mb-2 bg-red-400 text-white py-2 text-center font-bold rounded-md">
                إدارة الليرة السورية
            </div>

            <div class="table-responsive">
                <table class="w-full table table-bordered table-hover text-center" style="direction: rtl;">
                    <tbody>
                        @foreach ($sypRates as $rate)
                            {{-- تفعيل/إيقاف الصرف --}}
                            <tr class="bg-gray-100">
                                <td class="font-bold text-blue-600">سعر قص ليرة سورية</td>
                                <td>
                                    <button
                                        onclick="if (confirm('هل أنت متأكد من {{ $rate->is_active ? 'إيقاف' : 'تفعيل' }} الصرف؟')) { @this.call('toggleSypExchangeRate', {{ $rate->id }}) }"
                                        class="px-4 py-1 rounded text-white text-sm font-bold
                                            {{ $rate->is_active ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}">
                                        {{ $rate->is_active ? 'مفعل' : 'غير مفعل' }}
                                    </button>
                                </td>
                                <td>
                                    @if ($editingId === $rate->id && $editingField === 'exchange_rate_1')
                                        <input type="text" wire:model.defer="newValue" class="form-control d-inline-block w-auto">
                                        <button wire:click="updateExchangeRate({{ $rate->id }}, 'exchange_rate_1')" class="btn btn-primary btn-sm">حفظ</button>
                                        <button wire:click="cancelEditing" class="btn btn-secondary btn-sm">إلغاء</button>
                                    @else
                                        <button wire:click="startEditing({{ $rate->id }}, 'exchange_rate_1', {{ $rate->exchange_rate_1 }})" class="btn">
                                            {{ number_format($rate->exchange_rate_1, 0) }}
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            {{-- تحديد وقت العمل --}}
                            <tr>
                                <td class="font-bold text-blue-600 bg-gray-200">تحديد وقت عمل</td>
                                {{-- وقت البدء --}}
                                <td>
                                    <span class="font-bold text-blue-600">وقت البدء</span><br>
                                    @if ($editingId === $rate->id && $editingField === 'exchange_rate_start_time')
                                        <input type="time" step="60" wire:model.defer="newValue" class="form-control d-inline-block w-auto">
                                        <button wire:click="updateTime({{ $rate->id }}, 'exchange_rate_start_time')" class="btn btn-primary btn-sm">حفظ</button>
                                        <button wire:click="cancelEditing" class="btn btn-secondary btn-sm">إلغاء</button>
                                    @else
                                        <button wire:click="startEditing({{ $rate->id }}, 'exchange_rate_start_time', '{{ $rate->exchange_rate_start_time }}')" class="btn">
                                            {{ $rate->exchange_rate_start_time ? \Carbon\Carbon::parse($rate->exchange_rate_start_time)->format('H:i') : '--' }}
                                        </button>
                                    @endif
                                </td>

                                {{-- وقت الانتهاء --}}
                                <td>
                                    <span class="font-bold text-blue-600">وقت الانتهاء</span><br>
                                    @if ($editingId === $rate->id && $editingField === 'exchange_rate_end_time')
                                        <input type="time" step="60" wire:model.defer="newValue" class="form-control d-inline-block w-auto">
                                        <button wire:click="updateTime({{ $rate->id }}, 'exchange_rate_end_time')" class="btn btn-primary btn-sm">حفظ</button>
                                        <button wire:click="cancelEditing" class="btn btn-secondary btn-sm">إلغاء</button>
                                    @else
                                        <button wire:click="startEditing({{ $rate->id }}, 'exchange_rate_end_time', '{{ $rate->exchange_rate_end_time }}')" class="btn">
                                            {{ $rate->exchange_rate_end_time ? \Carbon\Carbon::parse($rate->exchange_rate_end_time)->format('H:i') : '--' }}
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
