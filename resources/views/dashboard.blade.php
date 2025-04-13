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

<div class="flex-wrap -p-8 d-flex justify-content-between">
    <!-- قسم أسعار العملات -->
    <div wire:poll.600ms style="flex: 2 8 45%;">
        <livewire:currency-rates />
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="w-1/2 mt-4 card">
        <div class="card-body">
            <!-- شبكة عرض النماذج -->
            <div style="display: grid;
                      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                      gap: 1.5rem;
                      direction: rtl;
                      align-items: start;">

                <!-- حوالة جديدة -->
                <div class="border border-blue-200 rounded-lg p-4 bg-white shadow-sm">
                    <h4 class="text-lg font-semibold mb-4 text-blue-800 border-b pb-2">
                        <i class="fas fa-exchange-alt mr-2"></i>
                        حوالة جديدة
                    </h4>
                    <x-transfer-form :currencies="$currencies" :destinations="$destinations" />
                </div>

                <!-- النموذج السوري -->
                <div class="border border-green-200 rounded-lg p-4 bg-white shadow-sm">
                    <h4 class="text-lg font-semibold mb-4 text-green-800 border-b pb-2">
                        <i class="fas fa-lira-sign mr-2"></i>
                        حوالة ليرة سورية
                    </h4>
                    <x-transfer-form-syp :destinations="$destinations" :currencies="$currencies" :exchangeRate="$exchangeRate" />
                </div>

                <!-- سند الصرف -->
                <div class="border border-purple-200 rounded-lg p-4 bg-white shadow-sm">
                    <h4 class="text-lg font-semibold mb-4 text-purple-800 border-b pb-2">
                        <i class="fas fa-receipt mr-2"></i>
                        سند صرف
                    </h4>
                    <x-transfer-form-exchange :currencies="$currencies" :destinations="$destinations"/>
                </div>

                <!-- الاعتماد -->
                <div class="border border-orange-200 rounded-lg p-4 bg-white shadow-sm">
                    <h4 class="text-lg font-semibold mb-4 text-orange-800 border-b pb-2">
                        <i class="fas fa-stamp mr-2"></i>
                        اعتماد الحوالات
                    </h4>
                    <x-TransferFormapproval :currencies="$currencies" :destinations="$destinations" />
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* تأثيرات مرئية بسيطة */
.border-b {
    border-bottom: 2px solid rgba(0, 0, 0, 0.1);
}

.shadow-sm {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.rounded-lg {
    border-radius: 12px;
}

@media (max-width: 768px) {
    .card-body > div {
        grid-template-columns: 1fr !important;
    }
}
</style>
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
