<x-app-layout>
    <x-slot name="header">

    </x-slot>
    <div class="py-6 mt-1">
    <div class="container p-6">
    @php
        // مصفوفة لتحويل الحالة الإنجليزية إلى العربية مع إعدادات اللون (لون الخلفية ولون النص)
        $statusMapping = [
            'Pending' => [
                'text' => 'إنتظار',
                'bg' => 'bg-yellow-200',
                'textColor' => 'text-yellow-800',
            ],
            'Delivered' => [
                'text' => 'مسلمة',
                'bg' => 'bg-green-200',
                'textColor' => 'text-green-800',
            ],
            'Frozen' => [
                'text' => 'مجمدة',
                'bg' => 'bg-blue-200',
                'textColor' => 'text-blue-800',
            ],
            'Cancelled' => [
                'text' => 'ملغاة',
                'bg' => 'bg-red-200',
                'textColor' => 'text-red-800',
            ],
        ];
    @endphp

    <!-- Modal for displaying the image -->
    <div id="imageModal" class="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center p-4 hidden ">
        <div class="relative bg-white rounded-lg shadow-2xl p-6 max-w-2xl w-full">
            <div class="border-4 border-blue-900 rounded-lg overflow-hidden shadow-lg">
                <img class="w-full h-auto" id="transferImage" src="" alt="Transfer Image">
            </div>
            <div class="flex justify-between mt-6 space-x-4 w-full">
                <button onclick="copyData()" class="flex-1 bg-blue-500 text-white px-6 py-2 rounded-lg">نسخ
                    البيانات</button>
                <button onclick="downloadImage()" class="flex-1 bg-green-500 text-white px-6 py-2 rounded-lg">تنزيل
                    الصورة</button>
                <a href="{{ route('transfers.sent.index') }}"
                    class="flex-1 text-center bg-red-500 text-white px-6 py-2 rounded-lg">إغلاق</a>
            </div>
        </div>
    </div>




    <!-- نافذة عرض تفاصيل الحوالة -->
    <div id="detailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white  rounded-2xl shadow-lg w-full max-w-5xl p-3 relative "> <!-- تكبير النافذة -->

            <!-- زر إغلاق -->


            <!-- عنوان النافذة -->
            <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">تفاصيل الحوالة</h2>

            <!-- تفاصيل الحوالة -->
            <div class="grid grid-cols-1 md:grid-cols-7 gap-3  px-4 mb-6 "> <!-- تحسين المسافات -->

                <div class="text-center">
                    <span class="block  bg-custom-gray text-black font-semibold rounded-lg">رقم الإشعار</span>

                    <span id="movementNumber" class="block  text-gray-700"></span>
                </div>

                <div class=" text-center ">
                    <span class="block  bg-custom-gray text-black  font-semibold rounded-lg">اسم المستلم</span>

                    <span id="recipientName" class="block  text-gray-700"></span>
                </div>

                <div class=" text-center ">
                    <span class="block  bg-custom-gray text-black  font-semibold rounded-lg">رقم الجوال</span>

                    <span id="recipientMobile" class="block  text-gray-700"></span>
                </div>

                <div class=" text-center ">
                    <span class="block  bg-custom-gray text-black  font-semibold rounded-lg">المبلغ المرسل</span>

                    <span id="sentAmount" class="block  text-gray-700"></span>
                </div>

                <div class=" text-center ">
                    <span class="block  bg-custom-gray text-black  font-semibold rounded-lg">الإجور</span>

                    <span id="fees" class="block  text-gray-700"></span>
                </div>

                <div class=" text-center ">
                    <span class="block  bg-custom-gray text-black  font-semibold rounded-lg">تاريخ الحوالة</span>

                    <span id="transferDate" class="block  text-center  text-gray-700"></span>
                </div>

                <div class=" text-center font-semibold text-gray-500 "> <!-- توسيع الملاحظة -->
                    <span class="block  bg-custom-gray text-black rounded-lg">ملاحظة</span>

                    <span id="note" class=" text-gray-800">لا توجد ملاحظات</span>
                </div>

            </div>

            <!-- صورة المستلم -->
            <div class="grid place-items-center ">
                <div class=" w-9/12 rounded-lg overflow-hidden border-2 h-80 border-gray-300 shadow-md mb-6">
                    <span class="block bg-custom-gray text-black text-center h-8">صورة</span>
                    <img id="recipientImage" src="" alt="صورة المستلم" class="w-full h-80 object-cover">
                </div>
            </div>
            <!-- زر الإغلاق -->
            <div class="flex justify-center text-white">
                <button id="closeDetailsModal"
                    class="bg-blue-900  w-full hover:bg-blue-900  hover:text-gray-300  px-6 py-2 rounded-lg transition">
                    إغلاق
                </button>
            </div>

        </div>
    </div>




    <div class="container mt-4">
        <div class="bg-white p-4 rounded-lg shadow-lg">
            <table class=" myTable table-auto w-full border border-gray-300 rounded-lg shadow-md overflow-hidden display"
                style="direction: rtl;">
                <thead class="bg-gray-200 text-gray-700 text-center">
                    <tr>
                        <th class="py-3 px-4 border-b text-center  w-72">الجهة المستقبلة</th>
                        <th class="py-3 px-4 border-b text-center">رقم إشعار</th>
                        <th class="py-3 px-4 border-b text-center">المستفيد</th>
                        <th class="py-3 px-4 border-b text-center">المبغ المرسل </th>
                        <th class="py-3 px-4 border-b text-center">المبلغ المستلم </th>
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
                            <!-- عمود الجهة المستقبلة -->
                            <td class="py-2 px-4 border-b font-bold">
                                @if ($transfer->recipient)
                                    {{ $transfer->recipient->name }}<br>
                                    {{ $transfer->recipient->country_user }} - {{ $transfer->recipient->state_user }}
                                @else
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b font-bold text-blue-500 text-center  ">
                                <div> {{ $transfer->movement_number }}</div>
                                <div>
                                    <button class="hover:bg-gray-300 text-black px-3 py-1 rounded view-image-btn"
                                        data-id="{{ $transfer->id }}">
                                        طباعة
                                    </button>
                                </div>
                            </td>
                            <td class="py-2 px-4 border-b text-center ">{{ $transfer->recipient_name }}</td>
                            <td class="py-2 px-4 border-b text-center ">
                                <div class="font-bold">
                                    {{ number_format($transfer->sent_amount, 2) }}
                                </div>
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
                            <td class="py-2 px-4 border-b text-center ">
                                <div class="font-bold">
                                    {{ number_format($transfer->received_amount, 2) }}
                                </div>
                                <div
                                    style="color: {{ $transfer->receivedCurrency
                                        ? ($transfer->receivedCurrency->name_ar == 'تركي'
                                            ? 'red'
                                            : ($transfer->receivedCurrency->name_ar == 'دولار'
                                                ? 'green'
                                                : 'inherit'))
                                        : 'inherit' }};">
                                    {{ $transfer->receivedCurrency? $transfer->receivedCurrency->name_ar : $transfer->received_currency }}
                                </div>
                            </td>
                            <td class="py-2 px-4 border-b text-center ">
                                <div class="font-bold">
                                    {{ number_format($transfer->fees, 2) }}
                                </div>
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
                            <td class="py-2 px-4 border-b text-center ">
                                @if (isset($statusMapping[$transfer->status]))
                                    <span
                                        class="{{ $statusMapping[$transfer->status]['bg'] }} {{ $statusMapping[$transfer->status]['textColor'] }} py-1 px-3 rounded-full inline-block">
                                        {{ $statusMapping[$transfer->status]['text'] }}
                                    </span>
                                @else
                                    {{ $transfer->status }}
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b text-center ">{{ $transfer->note }}</td>
                            <td class="py-2 px-4 border-b text-sm text-gray-500 text-center ">
                                {{ $transfer->created_at->format('Y-m-d H:i') }}
                            </td>

                            <td class="py-2 px-4 border-b text-center ">
                                <div class="flex justify-center items-center gap-1">
                                    @if ($transfer->status === 'Pending')
                                        <button class="text-black px-2 border-2 border-gray-300 py-1 rounded edit-btn"
                                            data-id="{{ $transfer->id }}" data-name="{{ $transfer->recipient_name }}"
                                            data-mobile="{{ $transfer->recipient_mobile }}">
                                            تعديل
                                        </button>
                                        <form action="{{ route('transfers.sent.destroy', $transfer->id) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-black border-2 border-gray-300 px-2 py-1 rounded"
                                                onclick="return confirm('هل أنت متأكد من إلغاء الحوالة؟')">
                                                إلغاء
                                            </button>
                                        </form>
                                    @endif

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
    <script>
        $(document).ready(function() {
            // عند الضغط على زر الطباعة
            $('.view-image-btn').click(function() {
                const transferId = $(this).data('id');

                // إرسال طلب للحصول على صورة الحوالة عبر AJAX
                $.ajax({
                    url: `/transfers/sent/${transferId}/print`,
                    type: 'GET',
                    success: function(response) {
                        $('#transferImage').attr('src', 'data:image/png;base64,' + response
                            .base64Image);
                        $('#imageModal').removeClass('hidden');
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#imageModal').addClass('hidden');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const mobile = $(this).data('mobile');

                Swal.fire({
                    title: 'تعديل الحوالة',
                    html: `
                        <form id="editTransferForm" method="POST" action="{{ route('transfers.sent.update', '') }}/${id}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="recipient_name" class="block text-gray-700 text-sm font-bold mb-2">اسم المستلم</label>
                                <input type="text" name="recipient_name" class="form-control w-full px-3 py-2 border rounded focus:ring focus:ring-blue-200" value="${name}" required>
                            </div>
                            <div class="mb-3">
                                <label for="recipient_mobile" class="block text-gray-700 text-sm font-bold mb-2">رقم جوال المستلم</label>
                                <input type="number" name="recipient_mobile" class="form-control w-full px-3 py-2 border rounded focus:ring focus:ring-blue-200" value="${mobile}" required>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'تحديث',
                    cancelButtonText: 'إلغاء',
                    preConfirm: () => {
                        $('#editTransferForm').submit();
                    }
                });
            });
        });
    </script>
    <script>
        // دالة لتنزيل الصورة عند الضغط على زر "تنزيل الصورة"
        function downloadImage() {
            const imageBase64 = document.getElementById('transferImage').src;

            if (!imageBase64) {
                alert('لا توجد صورة متاحة للتنزيل.');
                return;
            }

            // إنشاء رابط لتنزيل الصورة
            const link = document.createElement('a');
            link.href = imageBase64; // المصدر هو الصورة الموجودة في الـ img
            link.download = 'transfer_details.png'; // اسم الصورة عند تنزيلها
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.view-details-btn').click(function() {
                const transferId = $(this).data('id');

                $.ajax({
                    url: `/transfers/sent/${transferId}/details`,
                    type: 'GET',
                    success: function(response) {
                        let transfer = response.transfer;

                        // تحديث محتويات العناصر الموجودة في الـ Modal
                        $('#movementNumber').text(transfer.movement_number);
                        $('#recipientName').text(transfer.recipient_name);
                        $('#recipientMobile').text(transfer.recipient_mobile);
                        $('#sentAmount').text(parseFloat(transfer.sent_amount).toFixed(2));
                        $('#fees').text(parseFloat(transfer.fees).toFixed(2));
                        $('#note').text(transfer.note ?? '');
                        $('#transferDate').text(new Date(transfer.created_at).toLocaleString());

                        // تحديث صورة المستلم
                        $('#recipientImage').attr('src', response.image);

                        // عرض النافذة
                        $('#detailsModal').removeClass('hidden');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.error || 'حدث خطأ أثناء جلب البيانات');
                    }
                });
            });

            // إغلاق النافذة عند الضغط على زر "إغلاق"
            $('#closeDetailsModal').click(function() {
                $('#detailsModal').addClass('hidden');
            });
        });
    </script>

</x-app-layout>
