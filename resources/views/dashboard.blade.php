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
                        <div class="bg-light shadow-xl position-relative overflow-hidden"
                            style="flex-grow: 1; height: 35px; border-radius: 5px 0 0 5px;">
                            <div id="broadcastWrapper" class="position-absolute w-100 d-flex flex-column">
                                @foreach ($broadcastMessages as $index => $message)
                                    <div class="message py-2 text-center bg-white" style="height: 35px; display: none;">
                                        <span class="font-bold text-secondary"
                                            style="font-size: 1rem;">{{ $message->content }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <!-- صندوق عنوان الأخبار -->
                        <div class="news-container bg-primary d-flex justify-content-center align-items-center"
                            style="width: 150px; height: 35px; border-radius: 0 5px 5px 0;">
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


                
            </div>

            <!-- تضمين مكتبة Bootstrap JS -->
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
