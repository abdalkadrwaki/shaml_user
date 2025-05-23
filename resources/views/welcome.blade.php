<x-guest-layout>

    <x-authentication-card>

        <x-validation-errors class="mb-4 " />

        <x-slot name="logo" class="mt-[-80px]">

        </x-slot>


        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                {{ $value }}
            </div>
        @endsession
        <div class="flex justify-center items-center -mb-10">
            <img src="{{ asset('images/image-removebg-preview (2).png') }}" alt="Logo" width="300" height="300" class="transform translate-x-5">
        </div>


        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="relative mt-4">
                <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-gray-500">
                    <i class="fas fa-envelope text-blue-700"></i>
                </span>
                <x-input id="email"
                    class="block ps-10 w-full text-right placeholder:text-right"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="ادخل البريد الإلكتروني" />
            </div>

            <div class="relative mt-4">
                <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-gray-500">
                    <i class="fas fa-lock text-blue-700"></i>
                </span>
                <x-input id="password"
                    class="block ps-10 w-full text-right placeholder:text-right"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="ادخل كلمة المرور" />
            </div>


            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('ذكرني') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        href="{{ route('password.request') }}">
                        {{ __('نسيت كلمة السر؟') }}
                    </a>
                @endif

                <x-button class="ms-4">
                    {{ __('دخول') }}
                </x-button>
            </div>
        </form>


    </x-authentication-card>

</x-guest-layout>
