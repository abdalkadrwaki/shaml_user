<x-app-layout>
    <x-slot name="header">
        <!-- يمكن وضع أي محتوى للهيدر هنا -->
    </x-slot>

    <!-- القسم الرئيسي مع خلفية فاتحة وهامش علوي -->
    <div class="py-6 bg-gray-100 mt-4">
        <div class="container p-6 max-w-7xl mx-auto sm:px-6 lg:px-8">

            @php
                // استرجاع الرسائل النشطة من قاعدة البيانات
                $broadcastMessages = \App\Models\BroadcastMessage::where('is_active', true)->get();
            @endphp

            <!-- قسم رسائل الأخبار -->
            @if ($broadcastMessages->count() > 0)
                <div class="d-flex align-items-center justify-content-between mx-2 mt-3">
                    <!-- قسم الرسائل المتحركة -->
                    <div class="flex-grow-1 bg-light position-relative rounded-start" style="height: 35px; overflow: hidden;">
                        <div id="broadcastWrapper" class="position-absolute w-100 d-flex flex-column">
                            @foreach ($broadcastMessages as $index => $message)
                                <div class="py-2 text-center message bg-white" style="height: 35px; display: none;">
                                    <span class="text-black font-bold" style="font-size: 1rem;">{{ $message->content }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- قسم عنوان الأخبار -->
                    <div class="news-container bg-primary d-flex justify-content-center align-items-center rounded-end" style="width: 150px; height: 35px;">
                        <span class="text-white" style="font-size: 1rem;">أخبار الشركة</span>
                    </div>
                </div>
            @endif

            <!-- مكون Livewire لعرض الأرصدة -->
            <livewire:user-balances />

            <!-- عرض الأخطاء إن وجدت -->
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- تخصيص CSS للعرض على الشاشات الصغيرة -->
            <style>
                @media (max-width: 767px) {
                    #pills-tab {
                        width: 100% !important;
                        margin: 0 auto;
                    }
                    .d-flex {
                        flex-direction: column !important;
                    }
                    [wire\:poll\.600ms] {
                        flex: 0 0 100% !important;
                        max-width: 100% !important;
                    }
                    .w-1\/2 {
                        width: 100% !important;
                    }
                    .justify-content-between {
                        justify-content: center !important;
                    }
                }
            </style>

            <!-- قسم المحتوى الرئيسي -->
            <div class="container mt-4 max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="d-flex flex-wrap justify-content-between" style="direction: rtl;">

                    <!-- كرت التبويبات -->
                    <div class="w-1/2 mt-4 card">
                        <div class="card-body">
                            <!-- قائمة التبويبات -->
                            <ul class="nav nav-pills justify-content-center mb-3" id="pills-tab" role="tablist" style="width: 100%;">
                                <li class="nav-item flex-fill mx-1" role="presentation">
                                    <a class="nav-link bg-blue-900 text-white text-center px-2 py-2"
                                       id="pills-send-request-tab" data-bs-toggle="pill" href="#pills-send-request"
                                       role="tab" aria-controls="pills-send-request" aria-selected="true" style="width: 100%;">
                                        حوالة جديدة
                                    </a>
                                </li>
                                <li class="nav-item flex-fill mx-1" role="presentation">
                                    <a class="nav-link bg-blue-900 text-white text-center px-2 py-2"
                                       id="pills-SYP-tab" data-bs-toggle="pill" href="#pills-SYP"
                                       role="tab" aria-controls="pills-SYP" aria-selected="false" style="width: 100%;">
                                        سوري
                                    </a>
                                </li>
                                <li class="nav-item flex-fill mx-1" role="presentation">
                                    <a class="nav-link bg-blue-900 text-white text-center px-2 py-2"
                                       id="pills-payment-voucher-tab" data-bs-toggle="pill" href="#pills-payment-voucher"
                                       role="tab" aria-controls="pills-payment-voucher" aria-selected="false" style="width: 100%;">
                                        سند صرف
                                        <span class="badge bg-danger ms-1"></span>
                                    </a>
                                </li>
                                <li class="nav-item flex-fill mx-1" role="presentation">
                                    <a class="nav-link bg-blue-900 text-white text-center px-2 py-2"
                                       id="pills-approval-tab" data-bs-toggle="pill" href="#pills-approval"
                                       role="tab" aria-controls="pills-approval" aria-selected="false" style="width: 100%;">
                                        اعتماد
                                        <span class="badge bg-danger ms-1"></span>
                                    </a>
                                </li>
                            </ul>

                            <!-- محتوى التبويبات -->
                            <div class="tab-content" id="pills-tabContent" style="direction: rtl;">
                                <!-- تبويب "حوالة جديدة" -->
                                <div class="tab-pane fade" id="pills-send-request" role="tabpanel" aria-labelledby="pills-send-request-tab">
                                    <x-transfer-form :currencies="$currencies" :destinations="$destinations" />
                                </div>
                                <!-- تبويب "سند صرف" -->
                                <div class="tab-pane fade" id="pills-payment-voucher" role="tabpanel" aria-labelledby="pills-payment-voucher-tab">
                                    <x-transfer-form-exchange :currencies="$currencies" :destinations="$destinations" />
                                </div>
                                <!-- تبويب "اعتماد" -->
                                <div class="tab-pane fade" id="pills-approval" role="tabpanel" aria-labelledby="pills-approval-tab">
                                    <x-TransferFormapproval :currencies="$currencies" :destinations="$destinations" />
                                </div>
                                <!-- تبويب "سوري" -->
                                <div class="tab-pane fade" id="pills-SYP" role="tabpanel" aria-labelledby="pills-SYP-tab">
                                    <x-transfer-form-syp :destinations="$destinations" :currencies="$currencies" :exchangeRate="$exchangeRate" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- قسم أسعار العملات مع تحديث تلقائي -->
                    <div class="w-full" wire:poll.600ms style="flex: 2 8 45%;">
                        <livewire:currency-rates />
                    </div>
                </div>
            </div>

            <!-- تضمين مكتبة Bootstrap JS -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </div>
    </div>

    <!-- سكريبت لتدوير رسائل الأخبار -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message');
            if (messages.length === 0) return;
            let currentMessageIndex = 0;

            function showNextMessage() {
                messages[currentMessageIndex].style.display = 'none';
                currentMessageIndex = (currentMessageIndex + 1) % messages.length;
                messages[currentMessageIndex].style.display = 'block';
            }

            // عرض الرسالة الأولى فور تحميل الصفحة
            messages[currentMessageIndex].style.display = 'block';
            // التبديل بين الرسائل كل 3 ثواني
            setInterval(showNextMessage, 3000);
        });
    </script>
</x-app-layout>
