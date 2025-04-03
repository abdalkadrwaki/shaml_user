<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account\'s profile information and email address.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input type="file" id="photo" class="hidden"
                            wire:model.live="photo"
                            x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <x-label for="photo" value="{{ __('Photo') }}" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="rounded-full size-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block rounded-full size-20 bg-cover bg-no-repeat bg-center"
                          x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('Select A New Photo') }}
                </x-secondary-button>

                @if ($this->user->profile_photo_path)
                    <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                        {{ __('Remove Photo') }}
                    </x-secondary-button>
                @endif

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="grid grid-cols-7 gap-4">
            <!-- الحقل الأول: الاسم -->
            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" required autocomplete="name" />
                <x-input-error for="name" class="mt-2" />
            </div>

            <!-- الحقل الثاني: البريد الإلكتروني -->
            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required autocomplete="username" />
                <x-input-error for="email" class="mt-2" />
            </div>

            <!-- الحقل الثالث: الدولة (Country) -->
            <div>
                <x-label for="country_user" value="{{ __('Country') }}" />
                <x-input id="country_user" type="text" class="mt-1 block w-full" wire:model="state.country_user" required />
                <x-input-error for="country_user" class="mt-2" />
            </div>

            <!-- الحقل الرابع: الولاية (State) -->
            <div>
                <x-label for="state_user" value="{{ __('State') }}" />
                <x-input id="state_user" type="text" class="mt-1 block w-full" wire:model="state.state_user" required />
                <x-input-error for="state_user" class="mt-2" />
            </div>

            <!-- الحقل الخامس: رقم الرابط مع الأزرار -->
            <div x-data="{
                    copyLinkNumber() {
                        let input = $refs.linkNumberInput;
                        input.select();
                        document.execCommand('copy');
                        // يمكن إضافة إشعار هنا لإعلام المستخدم بأن الرقم تم نسخه
                    },
                    generateLinkNumber() {
                        let num = '';
                        for (let i = 0; i < 16; i++) {
                            num += Math.floor(Math.random() * 10);
                        }
                        $refs.linkNumberInput.value = num;
                        @this.set('state.link_number', num);
                    }
                }">
                <x-label for="link_number" value="{{ __('Link Number') }}" />
                <div class="flex">
                    <x-input id="link_number" type="text" class="mt-1 block w-full" wire:model="state.link_number"
                             x-ref="linkNumberInput" minlength="16" required />
                    <button type="button" @click="copyLinkNumber"
                            class="ml-2 bg-blue-500 text-white px-3 py-1 rounded">
                        {{ __('نسخ') }}
                    </button>
                    <button type="button" @click="generateLinkNumber"
                            class="ml-2 bg-green-500 text-white px-3 py-1 rounded">
                        {{ __('توليد') }}
                    </button>
                </div>
                <x-input-error for="link_number" class="mt-2" />
            </div>

            <!-- الحقل السادس: اسم المكتب -->
            <div>
                <x-label for="Office_name" value="{{ __('Office Name') }}" />
                <x-input id="Office_name" type="text" class="mt-1 block w-full" wire:model="state.Office_name" required />
                <x-input-error for="Office_name" class="mt-2" />
            </div>

            <!-- الحقل السابع: العنوان (يُستخدم textarea لاحتواء بيانات طويلة) -->
            <div>
                <x-label for="user_address" value="{{ __('العنوان') }}" />
                <textarea id="user_address" class="mt-1 block w-full" wire:model="state.user_address" required rows="3"></textarea>
                <x-input-error for="user_address" class="mt-2" />
            </div>
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
