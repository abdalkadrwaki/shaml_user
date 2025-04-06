<div class="min-h-screen flex justify-center items-center">
    <x-form-section submit="updateProfileInformation">
        <x-slot name="title">

        </x-slot>

        <x-slot name="description">

        </x-slot>

        <x-slot name="form">
            <!-- Profile Photo -->
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <div x-data="{ photoName: null, photoPreview: null }" class="col-span-6">
                    <!-- ... محتوى رفع الصورة الحالي ... -->
                </div>
            @endif

            <!-- الصف الأول - اسم المستخدم والإيميل -->
            <div class="col-span-6 grid grid-cols-6 gap-4">
                <!-- Name -->
                <div class="col-span-6 sm:col-span-3">
                    <x-label for="name" value="{{ __('Name') }}" />
                    <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" required
                        autocomplete="name" />
                    <x-input-error for="name" class="mt-2" />
                </div>

                <!-- Email -->
                <div class="col-span-6 sm:col-span-3">
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required
                        autocomplete="username" />
                    <x-input-error for="email" class="mt-2" />
                </div>
            </div>

            <!-- الصف الثاني - العنوان والبلد -->
            <div class="col-span-6 grid grid-cols-6 gap-4">
                <!-- User Address -->
                <div class="col-span-6 sm:col-span-3">
                    <x-label for="user_address" value="{{ __('User Address') }}" />
                    <x-input id="user_address" type="text" class="mt-1 block w-full" wire:model="state.user_address"
                        required />
                    <x-input-error for="user_address" class="mt-2" />
                </div>

                <!-- Country User -->
                <div class="col-span-6 sm:col-span-3">
                    <x-label for="country_user" value="{{ __('Country') }}" />
                    <x-input id="country_user" type="text" class="mt-1 block w-full" wire:model="state.country_user"
                        required />
                    <x-input-error for="country_user" class="mt-2" />
                </div>
            </div>

            <!-- الصف الثالث - الولاية ورقم الرابط -->
            <div class="col-span-6 grid grid-cols-6 gap-4">
                <!-- State User -->
                <div class="col-span-6 sm:col-span-3">
                    <x-label for="state_user" value="{{ __('State') }}" />
                    <x-input id="state_user" type="text" class="mt-1 block w-full" wire:model="state.state_user"
                        required />
                    <x-input-error for="state_user" class="mt-2" />
                </div>

                <!-- Link Number -->
                <div class="col-span-6 sm:col-span-3" x-data="{ generateNumber() { return Math.floor(1000000000000000 + Math.random() * 9000000000000000).toString().slice(0, 16); } }">
                    <x-label for="link_number" value="{{ __('Link Number') }}" />
                    <div class="flex gap-2">
                        <x-input id="link_number" type="text" class="mt-1 block w-full"
                            wire:model="state.link_number" required pattern="\d{16}" title="16 digits required"
                            maxlength="16" />

                        <x-button type="button" class="mt-1 whitespace-nowrap "
                            x-on:click="navigator.clipboard.writeText(document.getElementById('link_number').value)">
                            {{ __('نسخ') }}
                        </x-button>

                        <x-button type="button" class="mt-1 whitespace-nowrap"
                            x-on:click="document.getElementById('link_number').value = generateNumber()">
                            {{ __('توليد') }}
                        </x-button>
                    </div>

                    <x-input-error for="link_number" class="mt-2" />
                </div>
            </div>

            <!-- الصف الأخير - اسم المكتب (عرض كامل) -->
            <div class="col-span-6">
                <x-label for="Office_name" value="{{ __('Office Name') }}" />
                <x-input id="Office_name" type="text" class="mt-1 block w-full" wire:model="state.Office_name"
                    required />
                <x-input-error for="Office_name" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="me-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button wire:loading.attr="disabled" wire:target="photo">
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-form-section>
</div>
