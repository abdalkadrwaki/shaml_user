<x-app-layout>
    <x-slot name="header">

    </x-slot>
    <div class="py-6 mt-1">
    <div class="container p-6">
        @php
        $broadcastMessages = \App\Models\BroadcastMessage::where('is_active', true)->get();
    @endphp
    <!-- قسم رسائل الأخبار -->
    @if ($broadcastMessages->count() > 0)
        <div class="items-center flex-1 mx-2 -mt-8 text-center d-flex align-items-center">
            <div class="mt-0 overflow-hidden shadow-xl position-relative bg-light"
                style="flex-grow: 1; height: 35px;  border-radius: 5px 0px 0px 5px;">
                <div class=" position-absolute w-100 d-flex flex-column" id="broadcastWrapper">
                    @foreach ($broadcastMessages as $index => $message)
                        <div class="py-2 text-center message  bg-white " style="height: 35px; display: none;">
                            <span class="text-black  text-secondary text-center  font-bold "
                                style="font-size: 1rem;">{{ $message->content }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="news-container bg-primary d-flex justify-content-center align-items-center"
                style="width: 150px; height: 35px; border-radius: 0px 5px 5px 0px; margin-right: 0px;">
                <span class="text-white" style="font-size: 1rem;">أخبار الشركة</span>
            </div>
        </div>
    @endif



<livewire:user-balances />



@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Main Content Section -->
<div class="d-flex flex-wrap gap-3 justify-content-between">
    <!-- Currency Rates Section -->
    <div wire:poll.600ms class="flex-grow-1 flex-shrink-1" style="min-width: 300px; flex-basis: 100%;">
        <livewire:currency-rates />
    </div>

    <!-- Main Card Container -->
    <div class="flex-grow-1 flex-shrink-1 card mt-3" style="min-width: 300px; flex-basis: 100%;">
        <div class="card-body">
            <!-- Navigation Tabs -->
            <ul class="nav nav-pills justify-content-center mb-3 flex-wrap gap-2" id="pills-tab" role="tablist">
                <!-- New Transfer Tab -->
                <li class="nav-item flex-fill text-center" role="presentation">
                    <a class="nav-link text-white bg-blue-900" id="pills-send-request-tab" data-bs-toggle="pill"
                        href="#pills-send-request" role="tab" aria-controls="pills-send-request" aria-selected="true">
                        حوالة جديدة
                    </a>
                </li>

                <!-- SYP Tab -->
                <li class="nav-item flex-fill text-center" role="presentation">
                    <a class="nav-link text-white bg-blue-900" id="pills-SYP-tab" data-bs-toggle="pill"
                        href="#pills-SYP" role="tab" aria-controls="pills-SYP" aria-selected="false">
                        سوري
                    </a>
                </li>

                <!-- Payment Voucher Tab -->
                <li class="nav-item flex-fill text-center" role="presentation">
                    <a class="nav-link text-white bg-blue-900" id="pills-payment-voucher-tab" data-bs-toggle="pill"
                        href="#pills-payment-voucher" role="tab" aria-controls="pills-payment-voucher"
                        aria-selected="false">
                        سند صرف
                        <span class="badge bg-danger ms-1"></span>
                    </a>
                </li>

                <!-- Approval Tab -->
                <li class="nav-item flex-fill text-center" role="presentation">
                    <a class="nav-link text-white bg-blue-900" id="pills-approval-tab" data-bs-toggle="pill"
                        href="#pills-approval" role="tab" aria-controls="pills-approval" aria-selected="false">
                        اعتماد
                        <span class="badge bg-danger ms-1"></span>
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="pills-tabContent" style="direction: rtl;">
                <div class="tab-pane fade" id="pills-send-request" role="tabpanel" aria-labelledby="pills-send-request-tab">
                    <x-transfer-form :currencies="$currencies" :destinations="$destinations" />
                </div>

                <div class="tab-pane fade" id="pills-payment-voucher" role="tabpanel" aria-labelledby="pills-payment-voucher-tab">
                    <x-transfer-form-exchange :currencies="$currencies" :destinations="$destinations"/>
                </div>

                <div class="tab-pane fade" id="pills-approval" role="tabpanel" aria-labelledby="pills-approval-tab">
                    <x-TransferFormapproval :currencies="$currencies" :destinations="$destinations" />
                </div>

                <div class="tab-pane fade" id="pills-SYP" role="tabpanel" aria-labelledby="pills-SYP-tab">
                    <x-transfer-form-syp :destinations="$destinations" :currencies="$currencies" :exchangeRate="$exchangeRate" />
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    </div>

</div>
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

        // تحديث الفهرس للرسالة التالية
        currentMessageIndex = (currentMessageIndex + 1) % messages.length;

        // إظهار الرسالة التالية
        messages[currentMessageIndex].style.display = 'block';
    }

    // عرض الرسالة الأولى عند التحميل
    messages[currentMessageIndex].style.display = 'block';

    // التبديل بين الرسائل كل 3 ثواني
    setInterval(showNextMessage, 3000);
});

    </script>
</x-app-layout>
