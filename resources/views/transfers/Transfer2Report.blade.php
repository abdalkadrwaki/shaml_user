{{-- resources/views/transfers/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold  text-center text-xl  text-gray-800 leading-tight">
            كشف الحساب مكاتب
        </h2>
    </x-slot>



    <div class="container" style="direction: rtl; width: 98%">
        <div class=" bg-white p-6 rounded-xl shadow-sm border border-gray-100 mt-3">
            <!-- العنوان مع تحسين التسلسل الهرمي -->

            <!-- نموذج البحث مع تحسين التنسيق -->
            <form action="{{ route('transfers.index') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- حقل اختيار العملة -->
                    <div class="space-y-1">
                        <label for="currency" class="block text-sm font-medium text-gray-600">اختر العملة</label>
                        <select
                            name="currency"
                            id="currency"
                            class="form-select  w-full p-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition-all"
                        >
                            @foreach ($currencies as $currency)
                                <option
                                    value="{{ $currency->name_en }}"
                                    {{ $selectedCurrency == $currency->name_en ? 'selected' : '' }}
                                    class="hover:bg-blue-50"
                                >
                                    {{ $currency->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- حقل التاريخ من -->
                    <div class="space-y-1">
                        <label for="from_date" class="block text-sm font-medium text-gray-600">من تاريخ</label>
                        <input
                            type="date"
                            name="from_date"
                            id="from_date"
                            class="w-full p-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition-all"
                            value="{{ request('from_date', now()->format('Y-m-d')) }}"
                        >
                    </div>

                    <!-- حقل التاريخ إلى -->
                    <div class="space-y-1">
                        <label for="to_date" class="block text-sm font-medium text-gray-600">إلى تاريخ</label>
                        <input
                            type="date"
                            name="to_date"
                            id="to_date"
                            class="w-full p-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition-all"
                            value="{{ request('to_date', now()->format('Y-m-d')) }}"
                        >
                    </div>

                    <!-- زر البحث مع تأثيرات تفاعلية -->

                </div>
                <div class="  mt-2">
                    <button
                        type="submit"
                        class="w-full h-9 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-200 transform hover:scale-[1.02] shadow-sm">
                        بحث
                    </button>
                </div>
            </form>
        </div>
        <!-- نتائج البحث -->
        @if (request()->hasAny(['currency', 'from_date', 'to_date']))
            <div class="bg-white p-4 rounded-lg shadow-lg mt-3">
                <table id="myTable"
                    class="table-bordered table-striped w-full border border-gray-300 shadow-md overflow-hidden"
                   >
                    <thead class="bg-gray-200 text-gray-700 text-center">
                        <tr>
                            <th class="py-3 px-4  text-center">الجهة المرسلة</th>
                            <th class="py-3 px-4  text-center">رقم الإشعار</th>
                            <th class="py-3 px-4  text-center w-60">التاريخ</th>
                            <th class="py-3 px-4  text-center w-60">الوصف</th>
                            <th class="py-3 px-4  text-center">مدين (علي)</th>
                            <th class="py-3 px-4  text-center">دائن (لكم)</th>

                            <th class="py-3 px-4  text-center">الأجور</th>
                            <th class="py-3 px-4  text-center">الرصيد</th>
                        </tr>
                    </thead>

                    <tbody class=" font-bold">
                        @php
                            // نفترض أن الكنترولر يحسب لنا القيم التالية:
                            // $initialBalance, $finalBalance, $transferData
                            // ونحسب هنا المدين والدائن الإجمالي للفترة
                            $initialDebit = $initialBalance < 0 ? abs($initialBalance) : 0;
                            $initialCredit = $initialBalance > 0 ? $initialBalance : 0;
                            $totalDebit = 0;
                            $totalCredit = 0;
                        @endphp

                        @foreach ($transferData as $row)
                            @php
                                $transfer = $row['transfer'];
                                $fees = $transfer->fees ?? 0;

                                // تحديد القيم بناءً على نوع العملية
                                $debitAmount = $row['amount'] < 0 ? abs($row['amount']) : 0;
                                $creditAmount = $row['amount'] >= 0 ? $row['amount'] : 0;

                                // جمع المجاميع
                                $totalDebit += $debitAmount;
                                $totalCredit += $creditAmount;
                            @endphp

                            <tr class="text-center text-gray-600">
                                <td class="py-2 px-4 border-b font-bold">
                                    @if ($transfer->sender)
                                        {{ $transfer->sender->name }}-{{ $transfer->sender->state_user }}
                                    @else
                                        <span class="text-red-500">غير متوفر</span>
                                    @endif
                                </td>
                                <td class="py-2 px-4 border-b text-center text-blue-700">{{ $transfer->movement_number }}</td>
                                <td class="py-2 px-4 border-b text-center">
                                    {{ $transfer->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="py-2 px-4 border-b text-center">
                                    @if ($transfer->status == 'Archived')
                                        {{ $transfer->recipient_name }} (ملغاة)
                                        @if ($transfer->transaction_type === 'Exchange')
                                            - بسعر : {{ number_format($transfer->exchange_rate, 2) }}
                                        @endif
                                    @else
                                        {{ $transfer->recipient_name }}
                                        @if ($transfer->transaction_type === 'Exchange')
                                            - بسعر : {{ number_format($transfer->exchange_rate, 2) }}
                                        @endif
                                    @endif
                                </td>
                                <!-- عمود المدين -->
                                <td class="py-2 px-4 border-b text-danger">
                                    @if ($debitAmount)
                                        {{ number_format($debitAmount, 2) }}

                                    @endif
                                </td>
                                <!-- عمود الدائن -->
                                <td class="py-2 px-4 border-b text-success">
                                    @if ($creditAmount)
                                        {{ number_format($creditAmount, 2) }}


                                    @endif
                                </td>

                                <td class="py-2 px-4 border-b text-center">
                                    {{ number_format($fees, 2) }}
                                </td>
                                <td class="py-2 px-4 border-b text-center">
                                    {{ number_format($row['cumulative_balance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        @php
                            // إجماليات الفترة
                            $grandTotalDebit = $totalDebit + $initialDebit;
                            $grandTotalCredit = $totalCredit + $initialCredit;
                        @endphp

                        <!-- صف المجموع النهائي -->
                        <tr class="font-bold text-center bg-gray-200">
                            <th colspan="3" class="py-2 px-4 border-b">
                                المجموع
                            </th>
                            <th class="py-2 px-4 border-b text-danger">
                                {{ number_format($grandTotalDebit, 2) }}
                            </th>
                            <th class="py-2 px-4 border-b text-success">
                                {{ number_format($grandTotalCredit, 2) }}
                            </th>
                            <th colspan="3" class="py-2 px-4 border-b"></th>
                            <th class="py-2 px-4 border-b">
                                {{ number_format($finalBalance, 2) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                الرجاء اختيار معايير البحث وعرض البيانات
            </div>
        @endif
    </div>

    {{-- يمكنك تفعيل DataTables هنا أو في ملف جافاسكربت منفصل --}}



</x-app-layout>
