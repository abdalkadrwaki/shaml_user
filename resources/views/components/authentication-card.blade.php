<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-cover bg-center" style="background-image: url('{{ asset('images/background.jpg') }}');">
    <!-- يتم تحريك الشعار لأعلى بمقدار 80 بكسل -->
    <div class="mt-[-80px]">
        {{ $logo }}
    </div>

    <!-- إزالة الهامش السالب من نموذج تسجيل الدخول -->
    <div class="w-full sm:max-w-md px-6 py-3 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>
</div>

