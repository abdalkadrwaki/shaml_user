{{-- resources/views/transfers/received.blade.php --}}
<x-app-layout>
    <x-slot name="header ">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            الحوالات الواردة
        </h2>
    </x-slot>


    {{--     @livewire('exchange-rates') --}}
    @php
        // تحويل الحالة الإنجليزية إلى العربية مع إعدادات اللون (خلفية ونص)
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

    <div class="container mt-4" style="width: 98%">




        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <!-- رأس البطاقة -->
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">

            <button id="toggleCurrencyBtn" class="btn btn-outline-primary btn-sm">
                إظهار مجموع
            </button>
        </div>

        <!-- محتوى البطاقة (صناديق العملات) -->

        <div id="currencyBoxes" class="overflow-x-auto whitespace-nowrap p-4" style="display: none;">
            <div class="flex flex-nowrap gap-6 justify-center">
                @foreach ($groupedTransfers as $currencyName => $transfers)
                    @php
                        $totalAmount = $transfers->sum('sent_amount');
                        $formattedAmount = number_format($totalAmount, 2);

                        $colorClass = 'bg-gray-300 text-black border-gray-400';
                        if ($currencyName === 'دولار') {
                            $colorClass = 'bg-green-600 text-white border-green-700';
                        } elseif ($currencyName === 'تركي') {
                            $colorClass = 'bg-red-600 text-white border-red-700';
                        } elseif ($currencyName === 'يورو') {
                            $colorClass = 'bg-blue-600 text-white border-blue-700';
                        }
                    @endphp

                    @if ($totalAmount > 0)
                        <div class="bg-white shadow-md rounded-md flex-shrink-0 flex flex-col items-center text-center w-64 no-underline hover:no-underline">

                            <div class="w-full py-2 {{ $colorClass }} rounded-t-md">
                                <h2 class="text-xl font-bold">{{ $currencyName }}</h2>
                            </div>
                            <div class="w-full bg-custom-gray py-2 border-b {{ Str::after($colorClass, ' ') }}">
                                <h2 class="text-xl font-bold">{{ __('إجمالي الإرساليات') }}</h2>
                            </div>
                            <div class="w-auto p-1 m-2 rounded-md">
                                <p class="text-2xl mt-2 text-green-700">
                                    {{ $formattedAmount }}
                                </p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>


        <div class="bg-white p-4 rounded-lg shadow-lg">


            <table class=" myTable table-auto w-full border border-gray-300  shadow-md overflow-hidden"
                style="direction: rtl;">
                <thead class="bg-gray-200 text-gray-700 ">
                    <tr>
                        <!-- عمود جديد لاسم الجهة المرسلة -->
                        <th class="px-4 py-3 text-center  w-48"> الجهة المرسلة</th>
                        <th class="px-4 py-3 text-center ">رقم إشعار</th>
                        <th class="px-4 py-3 text-center ">المستفيد</th>
                        <th class="py-3 px-4 border-b text-center">المبغ المرسل </th>
                        <th class="py-3 px-4 border-b text-center">المبلغ المستلم </th>
                        <th class="px-4 py-3 text-center ">الإجور</th>
                        <th class="px-4 py-3 text-center ">الحالة</th>
                        <th class="px-4 py-3 text-center ">ملاحظة</th>
                        <th class="px-4 py-3 text-center ">تاريخ</th>
                        <th class="px-4 py-3 text-center w-72">الإجراءت </th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($receivedTransfers as $transfer)
                        <tr class="text-center text-gray-600 hover:bg-gray-100 transition {{ $transfer->status === 'Frozen' ? 'bg-gray-300' : '' }}"
                            data-transfer-id="{{ $transfer->id }}">

                            <!-- عمود اسم الجهة المرسلة -->
                            <td class="py-2 px-4 border-b font-bold">
                                @if ($transfer->sender)
                                    {{ $transfer->sender->name }}<br>
                                    {{ $transfer->sender->state_user }} - {{ $transfer->sender->country_user }}
                                @else
                                    <span class="text-red-500">غير متوفر</span>
                                @endif
                            </td>

                            <!-- عمود رقم الإشعار -->
                            <td class="py-2 px-4 border-b font-bold text-blue-500">
                                <div>{{ $transfer->movement_number }}</div>

                            </td>

                            <!-- باقي الأعمدة -->
                            <td class="py-2 px-4 border-b">{{ $transfer->recipient_name }}</td>
                            <td class="py-2 px-4 border-b">
                                <div class="font-bold">{{ number_format($transfer->sent_amount, 2) }}</div>
                                <div
                                    style="color:
                                    {{ $transfer->currency
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
                                    {{ $transfer->receivedCurrency ? $transfer->receivedCurrency->name_ar : $transfer->received_currency }}
                                </div>
                            </td>
                            <td class="py-2 px-4 border-b">
                                <div class="font-bold">{{ number_format($transfer->fees, 2) }}</div>
                                <div
                                    style="color:
                                    {{ $transfer->currency
                                        ? ($transfer->currency->name_ar == 'تركي'
                                            ? 'red'
                                            : ($transfer->currency->name_ar == 'دولار'
                                                ? 'green'
                                                : 'inherit'))
                                        : 'inherit' }};">
                                    {{ $transfer->currency ? $transfer->currency->name_ar : $transfer->sent_currency }}
                                </div>
                            </td>
                            <td class="py-2 px-4 border-b text-center status-cell">
                                @if (isset($statusMapping[$transfer->status]))
                                    <span
                                        class="{{ $statusMapping[$transfer->status]['bg'] }} {{ $statusMapping[$transfer->status]['textColor'] }} py-1 px-3 rounded-full inline-block">
                                        {{ $statusMapping[$transfer->status]['text'] }}
                                    </span>
                                @else
                                    {{ $transfer->status }}
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b text-center">{{ $transfer->note }}</td>
                            <td class="py-2 px-4 border-b text-center">{{ $transfer->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="py-2 px-4 border-b text-center">
                                <form action="{{ route('transfers.toggle-freeze', $transfer->id) }}" method="POST"
                                    class="toggle-freeze-form inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="px-2 py-1 rounded {{ $transfer->status === 'Frozen' ? 'bg-green-500 hover:bg-green-600' : 'bg-blue-500 hover:bg-blue-600' }} text-white">
                                        {{ $transfer->status === 'Frozen' ? 'الغاء التجميد' : 'تجميد' }}
                                    </button>
                                </form>

                                @if (in_array($transfer->status, ['Delivered', 'Cancelled']))
                                    <button class="btn btn-secondary" disabled>تسليم</button>
                                @else
                                    <button class="btn btn-primary deliver-btn px-2 py-1 rounded"
                                        data-transfer-id="{{ $transfer->id }}">تسليم</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>





        <div class="modal fade" id="deliverTransferModal" tabindex="-1" aria-labelledby="deliverTransferModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content bg-white rounded-lg shadow-xl">

                    <div class="modal-header bg-gray-100 p-4 rounded-t-lg  text-center">
                        <h5 id="deliverTransferModalLabel" class="modal-title text-xl font-semibold text-gray-800">
                            تسليم الحوالة
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>

                    <div class="modal-body p-4">

                        <div id="transferInfo"
                            class="grid grid-cols-2 md:grid-cols-6 gap-4 p-4 bg-blue-50 rounded-xl shadow-inner">

                            <div class="text-center space-y-2 col-span-1">
                                <span class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">
                                    الجهة</span>
                                <span id="modal_sender" class="block text-gray-700 font-medium"></span>
                            </div>


                            <div class="text-center space-y-2 col-span-1">
                                <span
                                    class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">رقم
                                    الإشعار</span>
                                <span id="modal_movement_number" class="block text-gray-700 font-medium"></span>
                            </div>

                            <div class="text-center space-y-2 col-span-1">
                                <span
                                    class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">المستفيد
                                </span>
                                <span id="modal_recipient_name" class="block text-gray-700 font-medium"></span>
                            </div>

                            <div class="text-center space-y-2 col-span-1">
                                <span
                                    class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">المبلغ
                                </span>
                                <span id="modal_sent_amount" class="block text-gray-700 font-medium"></span>
                            </div>

                            <div class="text-center space-y-2 col-span-1">
                                <span
                                    class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">الاجور</span>
                                <span id="modal_fees" class="block text-gray-700 font-medium"></span>
                            </div>


                            <div class="text-center space-y-2 col-span-1">
                                <span
                                    class="block bg-white text-blue-600 text-sm font-bold py-1 rounded-lg shadow-sm">تاريخ</span>
                                <span id="modal_created_at" class="block text-gray-700 font-medium"></span>
                            </div>
                        </div>

                        <div id="passwordSection" class="mb-3">
                            <label for="transferPassword" class="form-label text-gray-700">أدخل كلمة المرور</label>
                            <input type="number" class="form-control w-full p-2 border border-gray-300 rounded-lg"
                                id="transferPassword">
                            <div id="passwordError" class="text-red-500 text-sm mt-2" style="display: none;"></div>
                        </div>

                        <div id="deliverySection" style="display: none;">
                            <div class="flex flex-col gap-4">
                                <!-- الصف العلوي للبوكسين -->
                                <div class="flex flex-col md:flex-row gap-4 mt-3 ">
                                    <!-- بوكس الكاميرا -->
                                    <div class="flex-1 border border-gray-300 rounded-lg p-4 flex items-center justify-center"
                                        style="height: 300px;">
                                        <div id="cameraContainer" class="w-full h-full">
                                            <video id="video" width="100%" height="100%" autoplay
                                                style="display: none;"></video>
                                            <canvas id="canvas" width="640" height="480"
                                                style="display: none;"></canvas>
                                        </div>
                                    </div>

                                    <!-- بوكس الصورة -->
                                    <div class="flex-1 border border-gray-300 rounded-lg p-4 flex items-center justify-center"
                                        style="height: 300px;">
                                        <img id="capturedImage" src="" alt="صورة ملتقطة" class="max-w-full"
                                            style="display: none;">
                                        <div id="cameraPlaceholder" class="text-gray-500">الكاميرا غير مفعلة</div>
                                    </div>
                                </div>

                                <!-- الأزرار تحت البوكسين -->
                                <div class="mt-2 flex gap-2 ">
                                    <button id="captureBtn"
                                        class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-lg w-full">
                                        التقاط الصورة من الكاميرا
                                    </button>
                                    <button id="chooseFileBtn"
                                        class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-lg w-full">
                                        اختيار صورة من الملفات
                                    </button>
                                    <input type="file" id="fileInput" accept="image/*" style="display: none;">
                                </div>

                                <!-- الإينبوت تحت الأزرار -->
                                <div class="flex-1">
                                    <div class="mb-3">
                                        <label for="recipientInfo" class="form-label text-gray-700">معلومات
                                            التسليم</label>
                                        <input type="text"
                                            class="form-control w-full p-2 border border-gray-300 rounded-lg"
                                            id="recipientInfo" value="لايوجد">
                                    </div>
                                </div>
                            </div>
                            <div id="deliveryError" class="text-red-500 text-sm mt-2" style="display: none;"></div>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // تبديل إظهار/إخفاء صناديق العملات
        document.getElementById('toggleCurrencyBtn').addEventListener('click', function() {
            var currencyBoxes = document.getElementById('currencyBoxes');
            if (currencyBoxes.style.display === 'none' || currencyBoxes.style.display === '') {
                currencyBoxes.style.display = 'block';
                this.textContent = 'إخفاء العملات';
            } else {
                currencyBoxes.style.display = 'none';
                this.textContent = 'إظهار العملات';
            }
        });
    </script>
    <script>
        // المتغيرات العامة
        let selectedTransfer = null;
        let wrongAttempts = 0;
        const maxAttempts = 5;
        const blockDuration = 5 * 60 * 1000; // 5 دقائق

        // دوال التحقق من حالة الحظر باستخدام localStorage
        function isBlocked() {
            const blockUntil = localStorage.getItem('blockUntil');
            return blockUntil && Date.now() < parseInt(blockUntil);
        }

        function blockUser() {
            localStorage.setItem('blockUntil', Date.now() + blockDuration);
        }

        // الحدث الخاص بزر "تسليم الحركة"
        // تأكد من استخدام الفئة الصحيحة: ".deliver-btn"
        document.querySelectorAll('.deliver-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (isBlocked()) {
                    Swal.fire('تنبيه', 'تم حظرك من فتح هذه النافذة لمدة 5 دقائق بسبب محاولات خاطئة.',
                        'warning');
                    return;
                }
                const transferId = this.getAttribute('data-transfer-id');
                selectedTransfer = {
                    id: transferId,
                    row: this.closest('tr')
                };

                // إرسال طلب لتجميد الحركة قبل فتح النافذة
                fetch("{{ url('/transfers') }}/" + transferId + "/toggle-freeze", {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // تحديث واجهة المستخدم (مثلاً تغيير حالة التجميد في الصف)
                            const statusCell = selectedTransfer.row.querySelector('.status-cell');
                            if (statusCell) {
                                const statusSpan = statusCell.querySelector('span');
                                if (statusSpan) {
                                    if (data.newStatus === 'Frozen') {
                                        statusSpan.innerText = 'مجمدة';
                                        statusSpan.className =
                                            'bg-blue-200 text-blue-800 py-1 px-3 rounded-full inline-block';
                                    } else {
                                        statusSpan.innerText = 'إنتظار';
                                        statusSpan.className =
                                            'bg-yellow-200 text-yellow-800 py-1 px-3 rounded-full inline-block';
                                    }
                                }
                            }

                            // تعبئة بيانات الحوالة في النافذة
                            document.getElementById('modal_sender').innerText = selectedTransfer
                                .row.children[0].innerText.trim();
                            document.getElementById('modal_movement_number').innerText =
                                selectedTransfer
                                .row.children[1].innerText.trim();
                            document.getElementById('modal_recipient_name').innerText = selectedTransfer
                                .row.children[2].innerText.trim();
                            document.getElementById('modal_sent_amount').innerText = selectedTransfer
                                .row.children[3].innerText.trim();
                            document.getElementById('modal_fees').innerText = selectedTransfer
                                .row.children[4].innerText.trim();
                            document.getElementById('modal_created_at').innerText = selectedTransfer
                                .row.children[7].innerText.trim();

                            // إعادة تعيين أقسام النافذة (إظهار قسم التحقق وإخفاء قسم بيانات التسليم)
                            document.getElementById('passwordSection').style.display = 'block';
                            document.getElementById('deliverySection').style.display = 'none';
                            document.getElementById('transferPassword').value = '';
                            document.getElementById('passwordError').style.display = 'none';

                            // فتح النافذة بعد نجاح الطلب
                            const deliverTransferModal = new bootstrap.Modal(document.getElementById(
                                'deliverTransferModal'));
                            deliverTransferModal.show();
                        } else {
                            Swal.fire('خطأ', 'حدث خطأ أثناء تجميد الحركة.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('خطأ', 'حدث خطأ أثناء الاتصال بالخادم.', 'error');
                    });
            });
        });

        // إعدادات الكاميرا والتقاط الصورة
        let videoStream = null;
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const capturedImage = document.getElementById('capturedImage');
        const cameraPlaceholder = document.getElementById('cameraPlaceholder');

        function startCamera() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                        video: true
                    })
                    .then(stream => {
                        videoStream = stream;
                        video.srcObject = stream;
                        video.style.display = 'block';
                        cameraPlaceholder.style.display = 'none';
                    })
                    .catch(err => console.error("حدث خطأ أثناء تشغيل الكاميرا:", err));
            }
        }

        function stopCamera() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
                video.style.display = 'none';
                cameraPlaceholder.style.display = 'block';
            }
        }

        function resetCameraArea() {
            stopCamera();
            video.style.display = 'none';
            canvas.style.display = 'none';
            capturedImage.style.display = 'none';
            cameraPlaceholder.style.display = 'block';
            document.getElementById('captureBtn').innerText = 'التقاط الصورة من الكاميرا';
            document.getElementById('recipientInfo').value = '';
            document.getElementById('deliveryError').style.display = 'none';
        }

        document.getElementById('captureBtn').addEventListener('click', function() {
            if (this.innerText === 'التقاط الصورة من الكاميرا') {
                startCamera();
                this.innerText = 'التقاط الصورة الآن';
            } else {
                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const dataURL = canvas.toDataURL('image/png');
                capturedImage.src = dataURL;
                capturedImage.style.display = 'block';
                stopCamera();
                this.innerText = 'التقاط الصورة من الكاميرا';
            }
        });

        document.getElementById('chooseFileBtn').addEventListener('click', function() {
            document.getElementById('fileInput').click();
        });

        document.getElementById('fileInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    capturedImage.src = e.target.result;
                    capturedImage.style.display = 'block';
                    stopCamera();
                }
                reader.readAsDataURL(file);
            }
        });

        // زر "تسليم الحوالة" يجمع بين التحقق من كلمة المرور وإرسال بيانات التسليم
        document.getElementById('deliverTransferBtn').addEventListener('click', function() {
            // إذا لم يتم التحقق من كلمة المرور بعد
            if (document.getElementById('deliverySection').style.display === 'none') {
                const password = document.getElementById('transferPassword').value.trim();
                if (!password) return;

                fetch("{{ url('/transfers') }}/" + selectedTransfer.id + "/verify-password", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            password: password
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            wrongAttempts = 0;
                            // إظهار قسم بيانات التسليم وإخفاء قسم التحقق
                            document.getElementById('passwordSection').style.display = 'none';
                            document.getElementById('deliverySection').style.display = 'block';
                            resetCameraArea();
                        } else {
                            wrongAttempts++;
                            document.getElementById('passwordError').style.display = 'block';
                            document.getElementById('passwordError').innerText = 'كلمة المرور خاطئة.';
                            document.getElementById('transferPassword').value = '';
                            if (wrongAttempts >= maxAttempts) {
                                Swal.fire('تنبيه',
                                    'لقد تجاوزت عدد المحاولات المسموح بها. سيتم حظرك لمدة 5 دقائق.',
                                    'warning');
                                blockUser();
                                bootstrap.Modal.getInstance(document.getElementById('deliverTransferModal'))
                                    .hide();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('خطأ', 'حدث خطأ أثناء التحقق من كلمة المرور.', 'error');
                    });
            } else {
                // بعد التحقق يتم جمع بيانات التسليم
                const imageData = capturedImage.src;
                if (!imageData) {
                    document.getElementById('deliveryError').style.display = 'block';
                    document.getElementById('deliveryError').innerText =
                        'يرجى التقاط صورة أو اختيار صورة من الملفات.';
                    return;
                }
                const recipientInfo = document.getElementById('recipientInfo').value.trim();
                if (!recipientInfo) {
                    document.getElementById('deliveryError').style.display = 'block';
                    document.getElementById('deliveryError').innerText = 'يرجى إدخال معلومات التسليم.';
                    return;
                }
                document.getElementById('deliveryError').style.display = 'none';

                // تعطيل الزر أثناء المعالجة
                const deliverBtn = document.getElementById('deliverTransferBtn');
                deliverBtn.disabled = true;
                deliverBtn.innerText = 'جاري التسليم...';

                fetch("{{ url('/transfers') }}/" + selectedTransfer.id + "/deliver", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            imageData: imageData,
                            recipientInfo: recipientInfo
                        })
                    })
                    .then(response => response.json())
                    // بعد نجاح عملية تسليم الحوالة
                    .then(data => {
                        if (data.success) {
                            Swal.fire('تم!', 'تم تسليم الحوالة بنجاح.', 'success').then(() => {
                                // تحديث حالة الحوالة في الجدول
                                const statusCell = selectedTransfer.row.querySelector('.status-cell');
                                if (statusCell) {
                                    const statusSpan = statusCell.querySelector('span');
                                    if (statusSpan) {
                                        // تغيير النص والألوان لحالة "مسلمة"
                                        statusSpan.innerText = 'مسلمة';
                                        statusSpan.className =
                                            'bg-green-200 text-green-800 py-1 px-3 rounded-full inline-block';
                                    }

                                }

                                // تعطيل زر "تسليم الحركة" في الصف لمنع التسليم مرة أخرى
                                const deliverButton = selectedTransfer.row.querySelector(
                                    '.deliver-btn');
                                if (deliverButton) {
                                    deliverButton.disabled = true;
                                    deliverButton.innerText = 'مسلمة'; // يمكنك تغيير النص حسب الرغبة
                                    deliverButton.classList.remove(
                                        'btn-primary'); // إزالة فئة الأزرار النشطة
                                    deliverButton.classList.add(
                                        'btn-secondary'); // إضافة فئة زر معطل أو مخصص
                                }

                                // إغلاق النافذة الموحدة
                                bootstrap.Modal.getInstance(document.getElementById(
                                    'deliverTransferModal')).hide();

                            });
                        } else {
                            Swal.fire('خطأ', data.message || 'حدث خطأ أثناء تسليم الحوالة.', 'error');
                        }
                    })

                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('خطأ', 'حدث خطأ أثناء الاتصال بالخادم.', 'error');
                    })
                    .finally(() => {
                        deliverBtn.disabled = false;
                        deliverBtn.innerText = 'تسليم الحوالة';
                    });
            }
        });



        // التعامل مع نموذج تجميد/فك تجميد (عند الضغط على الزر داخل الجدول)
        document.querySelectorAll('.toggle-freeze-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const button = this.querySelector('button');
                const statusText = button.innerText.trim();
                const message = statusText === 'تجميد' ?
                    'هل أنت متأكد أنك تريد تجميد هذه الحوالة؟' :
                    'هل أنت متأكد أنك تريد فك تجميد هذه الحوالة؟';

                Swal.fire({
                    title: 'تأكيد',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم',
                    cancelButtonText: 'لا'
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch(this.action, {
                                method: 'PATCH',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({})
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    if (data.newStatus === 'Frozen') {
                                        button.innerText = 'الغاء التجميد';
                                        button.classList.remove('bg-blue-500',
                                            'hover:bg-blue-600');
                                        button.classList.add('bg-green-500',
                                            'hover:bg-green-600');
                                    } else {
                                        button.innerText = 'تجميد';
                                        button.classList.remove('bg-green-500',
                                            'hover:bg-green-600');
                                        button.classList.add('bg-blue-500',
                                            'hover:bg-blue-600');
                                    }
                                    const statusCell = form.closest('tr').querySelector(
                                        '.status-cell');
                                    if (statusCell) {
                                        const statusSpan = statusCell.querySelector('span');
                                        if (statusSpan) {
                                            statusSpan.innerText = data.newStatus === 'Frozen' ?
                                                'مجمدة' : 'إنتظار';
                                            statusSpan.className = data.newStatus === 'Frozen' ?
                                                'bg-blue-200 text-blue-800 py-1 px-2 rounded-full inline-block' :
                                                'bg-yellow-200 text-yellow-800 py-1 px-2 rounded-full inline-block';
                                        }
                                    }
                                    Swal.fire('تم!', 'تم تغيير حالة التجميد بنجاح.', 'success');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('خطأ!', 'حدث خطأ أثناء تغيير الحالة.', 'error');
                            });
                    }
                });
            });
        });
    </script>

</x-app-layout>
