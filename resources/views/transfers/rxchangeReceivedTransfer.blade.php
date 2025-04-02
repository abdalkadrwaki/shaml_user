<x-app-layout>
    <x-slot name="header">
        <!-- يمكنك وضع العنوان هنا -->
    </x-slot>




    @php

        $statusMapping = [
            'Pending'   => ['text' => 'إنتظار',   'bg' => 'bg-yellow-200', 'textColor' => 'text-yellow-800'],
            'Delivered' => ['text' => 'مسلمة',    'bg' => 'bg-green-200',  'textColor' => 'text-green-800'],
            'Frozen'    => ['text' => 'مجمدة',    'bg' => 'bg-blue-200',   'textColor' => 'text-blue-800'],
            'Cancelled' => ['text' => 'ملغاة',    'bg' => 'bg-red-200',    'textColor' => 'text-red-800'],
        ];
    @endphp

    <div class="container mt-4">
        <div class="bg-white p-4 rounded-lg shadow-lg">
            <table class="tebl table-auto w-full border border-gray-300 rounded-lg shadow-md overflow-hidden" style="direction: rtl;">
                <thead class="bg-gray-200 text-gray-700 text-center">
                    <tr>
                        <th class="py-3 px-4 text-center w-48">الجهة</th>
                        <th class="py-3 px-4 text-center">رقم حركة</th>
                        <th class="py-3 px-4 text-center">نوع حركة</th>
                        <th class="py-3 px-4 text-center">بيع مبلغ</th>
                        <th class="py-3 px-4 text-center">شراء مبلغ</th>
                        <th class="py-3 px-4 text-center">سعر الصرف</th>
                        <th class="py-3 px-4 text-center status-cell">الحالة</th>
                        <th class="py-3 px-4 text-center">ملاحظة</th>
                        <th class="py-3 px-4 text-center">تاريخ</th>
                        <th class="py-3 px-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receivedTransfers as $transfer)
                    <tr class="text-center text-gray-600 hover:bg-gray-100 transition">
                        <!-- الجهة المستقبلة -->
                        <td class="py-2 px-4 border-b font-bold">
                            @if ($transfer->sender)
                                {{ $transfer->sender->Office_name }}<br>
                                {{ $transfer->sender->country_user }} - {{ $transfer->sender->state_user }}
                            @else
                                -
                            @endif
                        </td>
                        <!-- رقم الإشعار -->
                        <td class="py-2 px-4 border-b font-bold text-blue-500">{{ $transfer->movement_number }}</td>
                        <!-- اسم المستلم -->
                        <td class="py-2 px-4 border-b">{{ $transfer->recipient_name }}</td>
                        <!-- المبلغ المرسل مع العملة -->
                        <td class="py-2 px-4 border-b text-center">
                            <div class="font-bold">{{ number_format($transfer->sent_amount, 2) }}</div>
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
                        <!-- المبلغ المستلم مع العملة -->
                        <td class="py-2 px-4 border-b text-center">
                            <div class="font-bold">{{ number_format($transfer->received_amount, 2) }}</div>
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
                        <td class="py-2 px-4 border-b text-center">{{ $transfer->exchange_rate }}</td>
                        <!-- الحالة مع فئة status-cell -->
                        <td class="py-2 px-4 border-b text-center status-cell">
                            @if (isset($statusMapping[$transfer->status]))
                                <span class="{{ $statusMapping[$transfer->status]['bg'] }} {{ $statusMapping[$transfer->status]['textColor'] }} py-1 px-3 rounded-full inline-block">
                                    {{ $statusMapping[$transfer->status]['text'] }}
                                </span>
                            @else
                                {{ $transfer->status }}
                            @endif
                        </td>
                        <!-- الملاحظة -->
                        <td class="py-2 px-4 border-b text-center">{{ $transfer->note }}</td>
                        <!-- التاريخ -->
                        <td class="py-2 px-4 border-b text-sm text-gray-500">
                            {{ $transfer->created_at->format('Y-m-d H:i') }}
                        </td>
                        <!-- الإجراءات -->
                        <td class="py-2 px-4 border-b text-center">
                            <div class="flex justify-center items-center gap-1">
                                @if (in_array($transfer->status, ['Delivered', 'Cancelled']))
                                    <button class="btn btn-secondary" disabled>تسليم</button>
                                @else
                                    <button class="btn btn-primary deliver-btn px-2 py-1 rounded"
                                        data-transfer-id="{{ $transfer->id }}">تسليم</button>
                                @endif

                                @if ($transfer->status === 'Delivered')
                                    <button class="text-white bg-indigo-500 px-2 py-1 rounded view-details-btn" data-id="{{ $transfer->id }}">
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

        <!-- نافذة تسليم الحوالة -->
        <div class="modal fade" id="deliverTransferModal" tabindex="-1" aria-labelledby="deliverTransferModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content bg-white rounded-lg shadow-xl">
                    <div class="modal-header bg-gray-100 p-4 rounded-t-lg text-center">
                        <h5 id="deliverTransferModalLabel" class="modal-title text-xl font-semibold text-gray-800">
                            تسليم الحوالة
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center ">
                            <span class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">
                                الجهة المرسلة</span>
                            <span id="modal_sender" class="block text-gray-700 font-medium"></span>
                        </div>
                        <div id="transferInfo"
                            class="grid grid-cols-2 md:grid-cols-6 gap-4 p-4 bg-blue-50 rounded-xl shadow-inner" style="direction: rtl;">

                            <div class="text-center space-y-2 col-span-1">
                                <span class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">رقم الإشعار</span>
                                <span id="modal_movement_number" class="block text-gray-700 font-medium"></span>
                            </div>
                            <div class="text-center space-y-2 col-span-1">
                                <span class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">نوع الحركة</span>
                                <span id="modal_recipient_name" class="block text-gray-700 font-medium"></span>
                            </div>
                            <div class="text-center space-y-2 col-span-1">
                                <span class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">بيع مبلغ</span>
                                <span id="modal_sent_amount" class="block text-gray-700 font-medium"></span>
                            </div>
                            <div class="text-center space-y-2 col-span-1">
                                <span class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">شراء مبلغ</span>
                                <span id="modal_received_amount" class="block text-gray-700 font-medium"></span>
                            </div>
                            <div class="text-center space-y-2 col-span-1">
                                <span class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">سعر الصرف</span>
                                <span id="modal_exchange_rate" class="block text-gray-700 font-medium"></span>
                            </div>
                            <div class="text-center space-y-2 col-span-1">
                                <span class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">تاريخ</span>
                                <span id="modal_created_at" class="block text-gray-700 font-medium"></span>
                            </div>
                        </div>
                        <!-- قسم التحقق من كلمة المرور -->
                        <div id="passwordSection" class="mb-3">
                            <label for="transferPassword" class="form-label text-gray-700">أدخل كلمة المرور</label>
                            <input type="number" class="form-control w-full p-2 border border-gray-300 rounded-lg"
                                id="transferPassword">
                            <div id="passwordError" class="text-red-500 text-sm mt-2" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-gray-100 p-1 rounded-b-lg flex justify-end gap-2">
                        <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg"
                            data-bs-dismiss="modal">
                            إغلاق
                        </button>
                        <button type="button" id="deliverTransferBtn"
                            class="bg-green-500 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                            تسليم الحوالة
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- تضمين مكتبات Bootstrap JS و SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- سكريبتات الجافاسكريبت -->
        <script>
            // المتغيرات العامة
            let selectedTransfer = null;
            let wrongAttempts = 0;
            const maxAttempts = 5;
            const blockDuration = 5 * 60 * 1000; // 5 دقائق

            function isBlocked() {
                const blockUntil = localStorage.getItem('blockUntil');
                return blockUntil && Date.now() < parseInt(blockUntil);
            }

            function blockUser() {
                localStorage.setItem('blockUntil', Date.now() + blockDuration);
            }

            // عند الضغط على زر "تسليم الحوالة"
            document.querySelectorAll('.deliver-btn').forEach(button => {
                button.addEventListener('click', function() {
                    if (isBlocked()) {
                        Swal.fire('تنبيه', 'تم حظرك من فتح هذه النافذة لمدة 5 دقائق بسبب محاولات خاطئة.', 'warning');
                        return;
                    }
                    const transferId = this.getAttribute('data-transfer-id');
                    selectedTransfer = {
                        id: transferId,
                        row: this.closest('tr')
                    };

                    // تعبئة بيانات الحوالة في النافذة
                    document.getElementById('modal_sender').innerText = selectedTransfer.row.children[0].innerText.trim();
                    document.getElementById('modal_movement_number').innerText = selectedTransfer.row.children[1].innerText.trim();
                    document.getElementById('modal_recipient_name').innerText = selectedTransfer.row.children[2].innerText.trim();
                    document.getElementById('modal_sent_amount').innerText = selectedTransfer.row.children[3].innerText.trim();
                    document.getElementById('modal_received_amount').innerText = selectedTransfer.row.children[4].innerText.trim();
                    document.getElementById('modal_exchange_rate').innerText = selectedTransfer.row.children[5].innerText.trim();
                    // استخدام الفهرس 8 للحصول على التاريخ
                    document.getElementById('modal_created_at').innerText = selectedTransfer.row.children[8].innerText.trim();

                    // إعادة تعيين أقسام النافذة (إظهار قسم التحقق)
                    document.getElementById('passwordSection').style.display = 'block';
                    document.getElementById('transferPassword').value = '';
                    document.getElementById('passwordError').style.display = 'none';

                    // فتح النافذة
                    const deliverTransferModal = new bootstrap.Modal(document.getElementById('deliverTransferModal'));
                    deliverTransferModal.show();
                });
            });

            // عند الضغط على زر "تسليم الحوالة" في النافذة
            document.getElementById('deliverTransferBtn').addEventListener('click', function() {
                const password = document.getElementById('transferPassword').value.trim();
                if (!password) return;

                // التحقق من كلمة المرور
                fetch("{{ url('/transfers/rxchangeReceivedTransfer') }}/" + selectedTransfer.id + "/verify-password", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ password: password })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        wrongAttempts = 0;
                        // إرسال طلب تسليم الحوالة بعد التحقق
                        fetch("{{ url('/transfers/rxchangeReceivedTransfer') }}/" + selectedTransfer.id + "/deliver", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ recipientInfo: 'تم التسليم' })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('تم!', 'تم تسليم الحوالة بنجاح.', 'success').then(() => {
                                    // تحديث حالة الحوالة في الجدول
                                    const statusCell = selectedTransfer.row.querySelector('.status-cell');
                                    if (statusCell) {
                                        const statusSpan = statusCell.querySelector('span');
                                        if (statusSpan) {
                                            statusSpan.innerText = 'مسلمة';
                                            statusSpan.className = 'bg-green-200 text-green-800 py-1 px-3 rounded-full inline-block';
                                        }
                                    }
                                    // تعطيل زر "تسليم الحوالة" في الصف
                                    const deliverButton = selectedTransfer.row.querySelector('.deliver-btn');
                                    if (deliverButton) {
                                        deliverButton.disabled = true;
                                        deliverButton.innerText = 'مسلمة';
                                        deliverButton.classList.remove('btn-primary');
                                        deliverButton.classList.add('btn-secondary');
                                    }
                                    // إغلاق النافذة
                                    bootstrap.Modal.getInstance(document.getElementById('deliverTransferModal')).hide();
                                });
                            } else {
                                Swal.fire('خطأ', data.message || 'حدث خطأ أثناء تسليم الحوالة.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('خطأ', 'حدث خطأ أثناء الاتصال بالخادم.', 'error');
                        });
                    } else {
                        wrongAttempts++;
                        document.getElementById('passwordError').style.display = 'block';
                        document.getElementById('passwordError').innerText = 'كلمة المرور خاطئة.';
                        document.getElementById('transferPassword').value = '';
                        if (wrongAttempts >= maxAttempts) {
                            Swal.fire('تنبيه', 'لقد تجاوزت عدد المحاولات المسموح بها. سيتم حظرك لمدة 5 دقائق.', 'warning');
                            blockUser();
                            bootstrap.Modal.getInstance(document.getElementById('deliverTransferModal')).hide();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('خطأ', 'حدث خطأ أثناء التحقق من كلمة المرور.', 'error');
                });
            });
        </script>
    </div>
</x-app-layout>
