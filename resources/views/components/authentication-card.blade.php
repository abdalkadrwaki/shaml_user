<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 -mt-20 sm:pt-0 bg-cover bg-center" style="background-image: url('{{ asset('images/background.jpg') }}');">
    <div>
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md -mt-10 px-6 py-3 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>
</div>
