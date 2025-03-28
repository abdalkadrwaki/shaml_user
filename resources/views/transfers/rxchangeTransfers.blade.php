<x-teacher-layout>
    <x-slot name="header">
        <!-- يمكنك وضع العنوان هنا -->
    </x-slot>

    @php
        // مصفوفة لتحويل الحالة الإنجليزية إلى العربية مع إعدادات اللون
        $statusMapping = [
            'Pending' => ['text' => 'إنتظار', 'bg' => 'bg-yellow-200', 'textColor' => 'text-yellow-800'],
            'Delivered' => ['text' => 'مسلمة', 'bg' => 'bg-green-200', 'textColor' => 'text-green-800'],
            'Frozen' => ['text' => 'مجمدة', 'bg' => 'bg-blue-200', 'textColor' => 'text-blue-800'],
            'Cancelled' => ['text' => 'ملغاة', 'bg' => 'bg-red-200', 'textColor' => 'text-red-800'],
        ];
    @endphp


    <div class="container mt-4">
        <div class="p-4 bg-white rounded-lg shadow-lg">
            <table class="w-full overflow-hidden border border-gray-300 rounded-lg shadow-md table-auto tebl"
                style="direction: rtl;">
                <thead class="text-center text-gray-700 bg-gray-200">
                    <tr>
                        <th class="w-48 px-4 py-3 text-center border-b">الجهة</th>
                        <th class="px-4 py-3 text-center border-b">رقم حركة</th>
                        <th class="px-4 py-3 text-center border-b">نوع حركة </th>
                        <th class="px-4 py-3 text-center border-b">بيع مبلغ</th>
                        <th class="px-4 py-3 text-center border-b">شراء مبلغ</th>
                        <th class="px-4 py-3 text-center border-b"> سعر الصرف</th>
                        <th class="px-4 py-3 text-center border-b"> الحالة </th>
                        <th class="px-4 py-3 text-center border-b"> ملاحظة </th>
                        <th class="px-4 py-3 text-center border-b">تاريخ</th>
                        <th class="px-4 py-3 text-center border-b">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receivedTransfers as $transfer)
                        <tr class="text-center text-gray-600 transition hover:bg-gray-100">
                            <!-- الجهة المستقبلة -->
                            <td class="px-4 py-2 font-bold border-b">
                                @if ($transfer->recipient)
                                    {{ $transfer->recipient->name }}<br>
                                    {{ $transfer->recipient->country_user }} - {{ $transfer->recipient->state_user }}
                                @else
                                    -
                                @endif
                            </td>
                            <!-- رقم الإشعار مع زر الطباعة -->
                            <td class="px-4 py-2 font-bold text-blue-500 border-b">{{ $transfer->movement_number }}</td>
                            <!-- اسم المستلم -->
                            <td class="px-4 py-2 border-b">{{ $transfer->recipient_name }}</td>
                            <!-- المبلغ المرسل مع العملة -->
                            <!-- للإرسال -->
                            <td class="px-4 py-2 text-center border-b">
                                <div class="font-bold">{{ number_format($transfer->sent_amount, 2) }}</div>
                                <div
                                    style="color: {{ $transfer->currency
                                        ? ($transfer->currency->name_ar == 'تركي'
                                            ? 'red'
                                            : ($transfer->currency->name_ar == 'دولار'
                                                ? 'green'
                                                : 'inherit'))
                                        : 'inherit' }};">
                                    {{ $transfer->currency ? $transfer->currency->name_ar : $transfer->sent_currency }}
                                </div>
                            </td>

                            <!-- للاستلام -->
                            <td class="px-4 py-2 text-center border-b">
                                <div class="font-bold">{{ number_format($transfer->received_amount, 2) }}</div>
                                <div
                                    style="color: {{ $transfer->receivedCurrency
                                        ? ($transfer->receivedCurrency->name_ar == 'تركي'
                                            ? 'red'
                                            : ($transfer->receivedCurrency->name_ar == 'دولار'
                                                ? 'green'
                                                : 'inherit'))
                                        : 'inherit' }};">
                                    {{ $transfer->receivedCurrency ? $transfer->receivedCurrency->name_ar : $transfer->received_currency }}
                                </div>
                            </td>

                            <td class="px-4 py-2 text-center border-b">{{ $transfer->exchange_rate }}</td>
                            <!-- الحالة -->
                            <td class="px-4 py-2 text-center border-b">
                                @if (isset($statusMapping[$transfer->status]))
                                    <span
                                        class="{{ $statusMapping[$transfer->status]['bg'] }} {{ $statusMapping[$transfer->status]['textColor'] }} py-1 px-3 rounded-full inline-block">
                                        {{ $statusMapping[$transfer->status]['text'] }}
                                    </span>
                                @else
                                    {{ $transfer->status }}
                                @endif
                            </td>
                            <!-- الملاحظة -->
                            <td class="px-4 py-2 text-center border-b">{{ $transfer->note }}</td>
                            <!-- التاريخ -->
                            <td class="px-4 py-2 text-sm text-gray-500 border-b">
                                {{ $transfer->created_at->format('Y-m-d H:i') }}
                            </td>
                            <!-- الإجراءات -->
                            <td class="px-4 py-2 text-center border-b">
                                <div class="flex items-center justify-center gap-1">
                                    @if ($transfer->status === 'Pending')
                                        <form action="{{ route('transfers.sentrxchange.destroy', $transfer->id) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-2 py-1 text-black border-2 border-gray-300 rounded"
                                                onclick="return confirm('هل أنت متأكد من إلغاء الحوالة؟')">
                                                إلغاء
                                            </button>
                                        </form>
                                    @endif

                                    @if ($transfer->status === 'Delivered')
                                        <button class="px-2 py-1 text-white bg-indigo-500 rounded view-details-btn"
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
            <!-- روابط الترقيم (Pagination) -->
            <div class="mt-4">
                {{ $receivedTransfers->links() }}
            </div>
        </div>
    </div>

    <!-- تضمين SweetAlert2 و jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- دالة نسخ البيانات (مثال على نسخ رابط الصورة) -->
    <script>
        function copyData() {
            var imageSrc = $('#transferImage').attr('src');
            if (!imageSrc) {
                alert('لا توجد بيانات للنسخ.');
                return;
            }
            navigator.clipboard.writeText(imageSrc).then(function() {
                alert('تم نسخ البيانات.');
            }, function(err) {
                console.error('خطأ أثناء النسخ: ', err);
            });
        }
    </script>

    <!-- جافاسكريبت لعرض الصورة وإغلاق النوافذ -->
    <script>
        $(document).ready(function() {
            // عرض صورة الحوالة عند الضغط على زر "طباعة"
            $('.view-image-btn').click(function() {
                const transferId = $(this).data('id');

                $.ajax({
                    url: `/transfers/sentrxchange/${transferId}/print`,
                    type: 'GET',
                    success: function(response) {
                        $('#transferImage').attr('src', 'data:image/png;base64,' + response
                            .base64Image);
                        $('#imageModal').removeClass('hidden');
                    },
                    error: function() {
                        alert('حدث خطأ أثناء جلب الصورة.');
                    }
                });
            });

            // إغلاق نافذة الصورة
            $('#closeModal').click(function() {
                $('#imageModal').addClass('hidden');
            });

            // عرض تفاصيل الحوالة عند الضغط على زر "التفاصيل"
            $('.view-details-btn').click(function() {
                const transferId = $(this).data('id');

                $.ajax({
                    url: `/transfers/sentrxchange/${transferId}/details`,
                    type: 'GET',
                    success: function(response) {
                        let transfer = response.transfer;
                        $('#movementNumber').text(transfer.movement_number);
                        $('#recipientName').text(transfer.recipient_name);
                        $('#recipientMobile').text(transfer.recipient_mobile);
                        $('#sentAmount').text(parseFloat(transfer.sent_amount).toFixed(2));
                        $('#fees').text(parseFloat(transfer.fees).toFixed(2));
                        $('#note').text(transfer.note ?? '');
                        $('#transferDate').text(new Date(transfer.created_at).toLocaleString());
                        $('#recipientImage').attr('src', response.image);
                        $('#detailsModal').removeClass('hidden');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.error || 'حدث خطأ أثناء جلب البيانات');
                    }
                });
            });

            // إغلاق نافذة التفاصيل
            $('#closeDetailsModal').click(function() {
                $('#detailsModal').addClass('hidden');
            });
        });

        // دالة تنزيل الصورة
        function downloadImage() {
            const imageBase64 = document.getElementById('transferImage').src;
            if (!imageBase64) {
                alert('لا توجد صورة متاحة للتنزيل.');
                return;
            }
            const link = document.createElement('a');
            link.href = imageBase64;
            link.download = 'transfer_details.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</x-teacher-layout>
