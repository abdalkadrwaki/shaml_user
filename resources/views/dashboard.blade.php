<x-app-layout>
    <x-slot name="header">
        <!-- يمكن إضافة عنوان الصفحة هنا إذا لزم الأمر -->
    </x-slot>

    <div class="container mt-4 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="py-3 mt-4">
            <div class="container p-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
                @php
                    // جلب رسائل النشرة الإخبارية النشطة
                    $broadcastMessages = \App\Models\BroadcastMessage::where('is_active', true)->get();
                @endphp

                <!-- قسم رسائل الأخبار -->
                @if ($broadcastMessages->count() > 0)
                    <div class="d-flex align-items-center flex-1 mx-2 -mt-8 text-center">
                        <!-- صندوق الرسائل -->
                        <div class="bg-light shadow-xl position-relative overflow-hidden" style="flex-grow: 1; height: 35px; border-radius: 5px 0 0 5px;">
                            <div id="broadcastWrapper" class="position-absolute w-100 d-flex flex-column">
                                @foreach ($broadcastMessages as $index => $message)
                                    <div class="message py-2 text-center bg-white" style="height: 35px; display: none;">
                                        <span class="font-bold text-secondary" style="font-size: 1rem;">{{ $message->content }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <!-- صندوق عنوان الأخبار -->
                        <div class="news-container bg-primary d-flex justify-content-center align-items-center" style="width: 150px; height: 35px; border-radius: 0 5px 5px 0;">
                            <span class="text-white" style="font-size: 1rem;">أخبار الشركة</span>
                        </div>
                    </div>
                @endif

                <!-- مكون رصيد المستخدم -->
                <livewire:user-balances />

                <!-- عرض الأخطاء إن وجدت -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- إضافة CSS خاص للتجاوب على الشاشات الصغيرة -->
                <style>
                    @media (max-width: 767px) {
                        /* توسيط تبويبات الصفحة */
                        #pills-tab {
                            width: 100% !important;
                            margin: 0 auto;
                        }
                        /* تغيير اتجاه الفليكس إلى عمودي */
                        .d-flex {
                            flex-direction: column !important;
                        }
                        /* جعل العناصر التي تُحدّث تلقائياً بعرض كامل */
                        [wire\:poll\.600ms] {
                            flex: 0 0 100% !important;
                            max-width: 100% !important;
                        }
                        /* ضبط عرض القسم الرئيسي بالكامل */
                        .w-1\/2 {
                            width: 100% !important;
                        }
                        /* تعديل توزيع المسافات بين الأعمدة */
                        .justify-content-between {
                            justify-content: center !important;
                        }
                    }
                </style>

                <!-- القسم الرئيسي للمحتوى -->
                <div class="d-flex flex-wrap justify-content-between" style="direction: rtl;">
                    <!-- بطاقة التبويبات -->
                    <div class="w-1/2 mt-4 card">
                        <div class="card-body">
                            <!-- قائمة التبويبات -->
                            <ul id="pills-tab" class="nav nav-pills p-1 mt-1 mb-3 justify-content-center" role="tablist" style="display: flex; width: 100%; justify-content: space-between;">
                                <!-- تبويب "حوالة جديدة" -->
                                <li class="nav-item" role="presentation" style="flex: 1; margin: 0 5px;">
                                    <a class="nav-link px-2 py-2 text-center text-white bg-blue-900 hover:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                       id="pills-send-request-tab" data-bs-toggle="pill" href="#pills-send-request"
                                       role="tab" aria-controls="pills-send-request" aria-selected="true" style="width: 100%;" data-bs-target="#pills-send-request">
                                        حوالة جديدة
                                    </a>
                                </li>
                                <!-- تبويب "سوري" -->
                                <li class="nav-item" role="presentation" style="flex: 1; margin: 0 5px;">
                                    <a class="nav-link px-2 py-2 text-center text-white bg-blue-900 hover:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                       id="pills-SYP-tab" data-bs-toggle="pill" href="#pills-SYP"
                                       role="tab" aria-controls="pills-SYP" aria-selected="true" style="width: 100%;" data-bs-target="#pills-SYP">
                                        سوري
                                    </a>
                                </li>
                                <!-- تبويب "سند صرف" -->
                                <li class="nav-item" role="presentation" style="flex: 1; margin: 0 5px;">
                                    <a class="nav-link px-2 py-2 text-center text-white bg-blue-900 hover:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                       id="pills-payment-voucher-tab" data-bs-toggle="pill" href="#pills-payment-voucher"
                                       role="tab" aria-controls="pills-payment-voucher" aria-selected="false" style="width: 100%;" data-bs-target="#pills-payment-voucher">
                                        سند صرف
                                        <span class="badge bg-danger ms-1"></span>
                                    </a>
                                </li>
                                <!-- تبويب "اعتماد" -->
                                <li class="nav-item" role="presentation" style="flex: 1; margin: 0 5px;">
                                    <a class="nav-link px-2 py-2 text-center text-white bg-blue-900 hover:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                       id="pills-approval-tab" data-bs-toggle="pill" href="#pills-approval"
                                       role="tab" aria-controls="pills-approval" aria-selected="false" style="width: 100%;" data-bs-target="#pills-approval">
                                        اعتماد
                                        <span class="badge bg-danger ms-1"></span>
                                    </a>
                                </li>
                            </ul>

                            <!-- محتوى التبويبات -->
                            <div class="tab-content" id="pills-tabContent" style="direction: rtl;">
                                <!-- محتوى تبويب "حوالة جديدة" -->
                                <div class="tab-pane fade" id="pills-send-request" role="tabpanel" aria-labelledby="pills-send-request-tab">
                                    <x-transfer-form :currencies="$currencies" :destinations="$destinations" />
                                </div>
                                <!-- محتوى تبويب "سند صرف" -->
                                <div class="tab-pane fade" id="pills-payment-voucher" role="tabpanel" aria-labelledby="pills-payment-voucher-tab">
                                    <x-transfer-form-exchange :currencies="$currencies" :destinations="$destinations" />
                                </div>
                                <!-- محتوى تبويب "اعتماد" -->
                                <div class="tab-pane fade" id="pills-approval" role="tabpanel" aria-labelledby="pills-approval-tab">
                                    <x-TransferFormapproval :currencies="$currencies" :destinations="$destinations" />
                                </div>
                                <!-- محتوى تبويب "سوري" -->
                                <div class="tab-pane fade" id="pills-SYP" role="tabpanel" aria-labelledby="pills-SYP-tab">
                                    <x-transfer-form-syp :destinations="$destinations" :currencies="$currencies" :exchangeRate="$exchangeRate" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- قسم أسعار العملات -->
                    <div class="flex-grow-2 flex-shrink-4 basis-[35%]" wire:poll.600ms>
                        <livewire:currency-rates />
                    </div>

                </div>
            </div>

            <!-- تضمين مكتبة Bootstrap JS -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </div>
    </div>

    <!-- سكربت لتناوب الرسائل الإخبارية -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message');
            if (messages.length === 0) {
                return;
            }
            let currentMessageIndex = 0;

            function showNextMessage() {
                // إخفاء الرسالة الحالية
                messages[currentMessageIndex].style.display = 'none';
                // تحديث الفهرس وإظهار الرسالة التالية
                currentMessageIndex = (currentMessageIndex + 1) % messages.length;
                messages[currentMessageIndex].style.display = 'block';
            }

            // عرض الرسالة الأولى فوراً
            messages[currentMessageIndex].style.display = 'block';
            // تغيير الرسائل كل 3 ثواني
            setInterval(showNextMessage, 3000);
        });
    </script>
</x-app-layout>
