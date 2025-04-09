<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-cover bg-center" style="background-image: url('{{ asset('images/background.jpg') }}');">
    <!-- الشعار يبقى معزوحاً لأعلى بمقدار 80 بكسل -->
    <div class="">
        {{ $logo }}
    </div>

    <!-- نموذج تسجيل الدخول مع ازاحة بسيطة لأعلى بمقدار 10 بكسل -->
    <div class="w-full sm:max-w-md mt-[-95px] px-6 py-3 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>
</div>
