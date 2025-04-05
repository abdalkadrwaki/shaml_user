<x-app-layout>
    <x-slot name="header">
        <!-- يمكنك وضع عنوان هنا -->
    </x-slot>
    <div class="py-6 mt-1">
        <div class="container p-6">
            @php
                $statusMapping = [

                    'Delivered' => [
                        'text' => 'مسلمة',
                        'bg' => 'bg-green-200',
                        'textColor' => 'text-green-800',
                    ],

                ];
            @endphp

            <!-- Modal التفاصيل -->
            

            <!-- جدول الحوالات -->
            <div class="container mt-4">
                <div class="bg-white p-4 rounded-lg shadow-lg">
                    <table class="myTable table-auto w-full border border-gray-300 rounded-lg shadow-md overflow-hidden display"
                        style="direction: rtl;">
                        <thead class="bg-gray-200 text-gray-700 text-center">
                            <tr class="display">
                                <th class="py-3 px-4 border-b text-center w-72">الجهة المستقبلة</th>
                                <th class="py-3 px-4 border-b text-center">رقم إشعار</th>
                                <th class="py-3 px-4 border-b text-center">المستفيد</th>
                                <th class="py-3 px-4 border-b text-center">المبلغ المرسل</th>
                                <th class="py-3 px-4 border-b text-center">المبلغ المستلم</th>
                                <th class="py-3 px-4 border-b text-center">الإجور</th>
                                <th class="py-3 px-4 border-b text-center">الحالة</th>
                                <th class="py-3 px-4 border-b text-center">ملاحظة</th>
                                <th class="py-3 px-4 border-b text-center w-48">تاريخ</th>
                                <th class="py-3 px-4 border-b text-center">الاجراءت</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transfers as $transfer)
                                <tr class="text-center text-gray-600 hover:bg-gray-100 transition">
                                    <td class="py-2 px-4 border-b font-bold">
                                        @if ($transfer->recipient)
                                            {{ $transfer->recipient->name }}<br>
                                            {{ $transfer->recipient->country_user }} -
                                            {{ $transfer->recipient->state_user }}
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border-b font-bold text-blue-500 text-center">
                                        {{ $transfer->movement_number }}
                                    </td>
                                    <td class="py-2 px-4 border-b text-center">{{ $transfer->recipient_name }}</td>
                                    <td class="py-2 px-4 border-b text-center">
                                        <div class="font-bold">
                                            {{ number_format($transfer->sent_amount, 2) }}
                                        </div>
                                        <div style="color: {{ $transfer->currency
                                            ? ($transfer->currency->name_ar == 'تركي'
                                                ? 'red'
                                                : ($transfer->currency->name_ar == 'دولار'
                                                    ? 'green'
                                                    : 'inherit'))
                                            : 'inherit' }};">
                                            {{ $transfer->currency ? $transfer->currency->name_ar : $transfer->sent_currency }}
                                        </div>
                                    </td>
                                    <td class="py-2 px-4 border-b text-center">
                                        <div class="font-bold">
                                            {{ number_format($transfer->received_amount, 2) }}
                                        </div>
                                        <div style="color: {{ $transfer->receivedCurrency
                                            ? ($transfer->receivedCurrency->name_ar == 'تركي'
                                                ? 'red'
                                                : ($transfer->receivedCurrency->name_ar == 'دولار'
                                                    ? 'green'
                                                    : 'inherit'))
                                            : 'inherit' }};">
                                            {{ $transfer->receivedCurrency ? $transfer->receivedCurrency->name_ar : $transfer->received_currency }}
                                        </div>
                                    </td>
                                    <td class="py-2 px-4 border-b text-center">
                                        <div class="font-bold">
                                            {{ number_format($transfer->fees, 2) }}
                                        </div>
                                        <div style="color: {{ $transfer->currency
                                            ? ($transfer->currency->name_ar == 'تركي'
                                                ? 'red'
                                                : ($transfer->currency->name_ar == 'دولار'
                                                    ? 'green'
                                                    : 'inherit'))
                                            : 'inherit' }};">
                                            {{ $transfer->currency ? $transfer->currency->name_ar : $transfer->sent_currency }}
                                        </div>
                                    </td>
                                    <td class="py-2 px-4 border-b text-center">
                                        @if (isset($statusMapping[$transfer->status]))
                                            <span class="{{ $statusMapping[$transfer->status]['bg'] }} {{ $statusMapping[$transfer->status]['textColor'] }} py-1 px-3 rounded-full inline-block">
                                                {{ $statusMapping[$transfer->status]['text'] }}
                                            </span>
                                        @else
                                            {{ $transfer->status }}
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border-b text-center">{{ $transfer->note }}</td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-500 text-center">
                                        {{ $transfer->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="py-2 px-4 border-b text-center">
                                        <div class="flex justify-center items-center gap-1">
                                            @if ($transfer->status === 'Delivered')
                                                <button class="text-white bg-indigo-500 px-2 py-1 rounded view-details-btn"
                                                    data-id="{{ $transfer->id }}">
                                                    التفاصيل
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- تضمين SweetAlert2 و jQuery -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        </div>
    </div>
</x-app-layout>
