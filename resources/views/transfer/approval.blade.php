
<div class="p-4 rounded bg-custom-gray">

    {{-- عرض الأخطاء باستخدام Swal في حال وجودها --}}
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'حدث خطأ!',
                    html: `
                    <div class="text-right font-cairo">
                        <ul class="list-none p-0">
                            @foreach ($errors->all() as $error)
                                <li class="mb-2 text-red-800 bg-red-100 p-2 rounded-md border border-red-300 shadow-sm">
                                    <i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                `,
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#d33',
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'rounded-xl shadow-lg'
                    }
                });
            });
        </script>
    @endif

    @if (session('transfer'))
        <script>
            function escapeHtml(unsafe) {
                if (typeof unsafe !== "string") {
                    unsafe = String(unsafe);
                }
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            document.addEventListener('DOMContentLoaded', function() {
                const transfer = @json(session('transfer'));
                const destinations = @json($destinations);

                let destinationText = transfer.destination;
                if (destinations && destinations.length) {
                    const selectedDestination = destinations.find(dest => dest.id == transfer.destination);
                    if (selectedDestination) {
                        destinationText =
                            `${selectedDestination.Office_name} - ${selectedDestination.state_user} - ${selectedDestination.country_user}`;
                    }


                }

                Swal.fire({
                    icon: 'success',
                    title: 'تم إرسال اعتماد بنجاح',
                    html: `
                    <div class="font-cairo max-w-full mx-auto"style="direction: rtl;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 p-4  rounded-xl shadow-lg">
                            <div class="text-center p-3 bg-white rounded-lg shadow-md transform hover:scale-105 transition duration-300">
                                <i class="fas fa-university text-blue-500 text-xl mb-2"></i>
                                <strong class="block text-gray-800 text-base">الجهة</strong>
                                <p class="text-gray-600">${escapeHtml(destinationText)}</p>
                            </div>

                            <div class="text-center p-3 bg-white rounded-lg shadow-md transform hover:scale-105 transition duration-100">
                                <i class="fas fa-money-bill-wave text-yellow-500 text-xl mb-2"></i>
                                <strong class="block text-gray-800 text-base">المبلغ المرسل</strong>
                                <p class="text-gray-600">${escapeHtml(String(transfer.sent_amount))}</p>
                                  <p class="text-gray-600">${escapeHtml(transfer.sent_currency)}</p>
                            </div>
                            ${transfer.note && transfer.note.trim() !== '' ? `
                                                    <div class="text-center p-3 bg-white rounded-lg shadow-md transform hover:scale-105 transition duration-300">
                                                        <i class="fas fa-sticky-note text-purple-500 text-xl mb-2"></i>
                                                        <strong class="block text-gray-800 text-base">ملاحظة</strong>
                                                        <p class="text-gray-600">${escapeHtml(transfer.note)}</p>
                                                    </div>` : ''}
                        </div>
                    </div>
                `,
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#3085d6',
                    width: '40%',
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl',
                        title: 'text-xl font-bold text-gray-900',
                        confirmButton: 'px-4 py-2 text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition duration-300'
                    }
                });
            });
        </script>
    @endif
    <form id="Approval-form" method="POST" action="{{ route('approval.submit') }}">
        @csrf

        <!-- اختيار الجهة -->
        <div class="mb-3 row">
            <div class="col-md-12">
                <label for="destination" class="form-label">الجهة</label>
                <select id="destination" name="destination" class="form-select js-example-basic-single" required>
                    <option value="">اختر الجهة</option>
                    @foreach ($destinations as $destination)
                        <option value="{{ $destination['id'] }}"
                            {{ old('destination') == $destination['id'] ? 'selected' : '' }}>
                            {{ $destination['Office_name'] }} - {{ $destination['state_user'] }} -
                            {{ $destination['country_user'] }}
                            {{ number_format($destination['balance'], 0) }}
                        </option>
                    @endforeach
                </select>
                @error('destination')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="destination_error" class="text-danger"></span>
            </div>
        </div>

        <!-- اختيار العملة والمبلغ -->
        <div class="mb-3 row">
            <div class="col-md-6">
                <label for="sent_currency" class="form-label">العملة المرسلة</label>
                <select id="sent_currency_approval" class="form-select" name="sent_currency" required>
                    <option value="">اختر العملة المرسلة</option>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency['name_en'] }}">
                            {{ $currency['name_ar'] }}
                        </option>
                    @endforeach
                </select>
                @error('sent_currency')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="sent_currency_error" class="text-danger"></span>
            </div>
            <div class="col-md-6">
                <label for="sent_amount" class="form-label">المبلغ المرسل</label>
                <input type="text" id="sent_amount_approval" class="form-control number-only" name="sent_amount"
                    min="0.01" step="0.01" required>
                @error('sent_amount')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="sent_amount_error" class="text-danger"></span>
            </div>
        </div>

        <!-- الملاحظة -->
        <div class="mb-3 row">
            <div class="col-md-12">
                <label for="note" class="form-label">ملاحظة</label>
                <textarea id="note_approval" class="form-control" name="note"></textarea>
                <span id="note_error" class="text-danger"></span>
            </div>
        </div>

        <!-- زر الإرسال -->
        <div class="row">
            <div class="text-center col-md-12">
                <button type="submit" class="w-full btn btn-success">إرسال الحوالة</button>
            </div>
        </div>
    </form>
</div>
