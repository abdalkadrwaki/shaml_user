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
    <div class="flex-wrap -p-8 d-flex justify-content-between">
        <!-- Currency Rates Section -->
        <div wire:poll.600ms style="flex: 2 8 45%;">
            <livewire:currency-rates />
        </div>

        <!-- Main Card Container -->
        <div class="w-1/2 mt-4 card">
            <div class="card-body">
                <!-- Navigation Tabs -->
                <ul class="p-1 mt-1 mb-3 nav nav-pills justify-content-center " id="pills-tab"
                    role="tablist"
                    style="display: flex; width: 100%; justify-content: space-between;">
                    <!-- New Transfer Tab -->
                    <li class="nav-item" role="presentation" style="flex: 1; margin: 0 5px;">
                        <a class="px-2 py-2 text-center text-white bg-blue-900 nav-link hover:border-blue-500 focus:ring-2 focus:ring-blue-500 aria-selected:bg-blue-700"
                            id="pills-send-request-tab" data-bs-toggle="pill" href="#pills-send-request" role="tab"
                            aria-controls="pills-send-request" aria-selected="true" style="width: 100%;"
                            data-bs-target="#pills-send-request">
                            حوالة جديدة
                        </a>
                    </li>
                    <li class="nav-item" role="presentation" style="flex: 1; margin: 0 5px;">
                        <a class="px-2 py-2 text-center text-white bg-blue-900 nav-link hover:border-blue-500 focus:ring-2 focus:ring-blue-500 aria-selected:bg-blue-700"
                            id="pills-SYP-tab" data-bs-toggle="pill" href="#pills-SYP" role="tab"
                            aria-controls="pills-SYP" aria-selected="true" style="width: 100%;"
                            data-bs-target="#pills-SYP">
                            سوري
                        </a>
                    </li>
                    <!-- Payment Voucher Tab -->
                    <li class="nav-item" role="presentation" style="flex: 1; margin: 0 5px;">
                        <a class="px-2 py-2 text-center text-white bg-blue-900 nav-link hover:border-blue-500 focus:ring-2 focus:ring-blue-500 aria-selected:bg-blue-700"
                            id="pills-payment-voucher-tab" data-bs-toggle="pill" href="#pills-payment-voucher"
                            role="tab" aria-controls="pills-payment-voucher" aria-selected="false" style="width: 100%;"
                            data-bs-target="#pills-payment-voucher">
                            سند صرف
                            <span class="badge bg-danger ms-1"></span>
                        </a>
                    </li>

                    <!-- Approval Tab -->
                    <li class="nav-item" role="presentation" style="flex: 1; margin: 0 5px;">
                        <a class="px-2 py-2 text-center text-white bg-blue-900 nav-link hover:border-blue-500 focus:ring-2 focus:ring-blue-500 aria-selected:bg-blue-700"
                            id="pills-approval-tab" data-bs-toggle="pill" href="#pills-approval" role="tab"
                            aria-controls="pills-approval" aria-selected="false" style="width: 100%;"
                            data-bs-target="#pills-approval">
                            اعتماد
                            <span class="badge bg-danger ms-1"></span>
                        </a>
                    </li>

                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="pills-tabContent" style="direction: rtl;">
                    <!-- Transfer Tab Content -->
                    <div class="tab-pane fade" id="pills-send-request" role="tabpanel"
                        aria-labelledby="pills-send-request-tab">
                        @each('Transfer.transfer', $currencies, 'currency')

                    </div>


                    <div class="tab-pane fade" id="pills-payment-voucher" role="tabpanel"
                        aria-labelledby="pills-payment-voucher-tab">

                    </div>

                    <!-- Approval Tab Content -->
                    <div class="tab-pane fade" id="pills-approval" role="tabpanel" aria-labelledby="pills-approval-tab">

                    </div>

                    <div class="tab-pane fade" id="pills-SYP" role="tabpanel" aria-labelledby="pills-SYP-tab">

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
        console.warn("لا توجد عناصر بالصفحة تحتوي على الصنف .message");
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
