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
                    <x-transfer-form :currencies="$currencies" :destinations="$destinations" />
                </div>

                <div class="tab-pane fade" id="pills-payment-voucher" role="tabpanel"
                    aria-labelledby="pills-payment-voucher-tab">

                    <div class="p-4 rounded bg-custom-gray">
                        <!-- عرض الأخطاء إن وجدت -->
                        <form action="{{ route('exchange.submit') }}" method="POST">
                            @csrf

                            <!-- اختيار الجهة -->
                            <div class="mb-3 row">
                                <div class="col-md-12">
                                    <label for="destination_exchange" class="form-label">الجهة</label>
                                    <select id="destination_exchange" name="destination_exchange" class="form-select js-example-basic-single"
                                        required>
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
                                    @error('destination_exchange')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- اختيار العملة المرسلة ومبلغ البيع -->
                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label for="sent_currency_exchange" class="form-label"> بيع العملة</label>
                                    <select id="sent_currency_exchange" class="form-select" name="sent_currency" required>
                                        <option value="" disabled selected> بيع العملة</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency['name_en'] }}"
                                                {{ old('sent_currency') == $currency['name_en'] ? 'selected' : '' }}>
                                                {{ $currency['name_ar'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sent_currency')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="sent_amount_exchange" class="form-label">بيع مبلغ</label>
                                    <input type="text" id="sent_amount_exchange" class="form-control number-only" name="sent_amount"
                                        lang="en" step="any" value="{{ old('sent_amount') }}" required>
                                    <div id="balanceError" class="mt-2 text-danger" style="display: none;">
                                        رصيدك في العملة المحددة غير كافٍ.
                                    </div>
                                    @error('sent_amount')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- اختيار العملة المستلمة ومبلغ الشراء -->
                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label for="received_currency_exchange" class="form-label"> شراء العملة</label>
                                    <select id="received_currency_exchange" class="form-select" name="received_currency" required>
                                        <option value="" disabled selected>اختر العملة المستلمة</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency['name_en'] }}"
                                                {{ old('received_currency') == $currency['name_en'] ? 'selected' : '' }}>
                                                {{ $currency['name_ar'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('received_currency')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="received_amount_exchange" class="form-label">شراء مبلغ</label>
                                    <input type="text" id="received_amount_exchange"
                                        class="text-gray-700 bg-gray-200 border border-gray-300 rounded-md form-control focus:bg-gray-200 focus:border-gray-300 focus:ring-0 focus:outline-none number-only"
                                        name="received_amount" value="{{ old('received_amount') }}" readonly>
                                    @error('received_amount')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- سعر الصرف -->
                            <div class="mb-3 row">
                                <div class="col-md-12">
                                    <label for="exchange_rate_exchange" class="form-label">الصرف</label>
                                    <input type="text" id="exchange_rate_exchange" class="form-control number-only" name="exchange_rate"
                                        value="{{ old('exchange_rate', 0) }}" required>
                                    @error('exchange_rate')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- الملاحظة -->
                            <div class="mb-3 row">
                                <div class="col-md-12">
                                    <label for="note_exchange" class="form-label">ملاحظة</label>
                                    <textarea id="note_exchange" class="form-control" name="note">{{ old('note') }}</textarea>
                                    @error('note')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- زر الإرسال -->
                            <div class="row">
                                <div class="text-center col-md-12">
                                    <button type="submit" class="w-full btn btn-success" id="submitBtn">
                                        إرسال الحوالة
                                    </button>
                                    <script>
                                        document.querySelector('form').addEventListener('submit', function(e) {
                                            const btn = document.getElementById('submitBtn');
                                            btn.disabled = true;
                                            btn.innerHTML = 'جارِ الإرسال...';
                                        });
                                    </script>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- جافاسكريبت جلب الرصيد عند تغيير العملة المرسلة -->

                    <script>
                        document.getElementById('sent_currency_exchange').addEventListener('change', function() {
                            const currency = this.value; // العملة المحددة
                            const destinationId = document.getElementById('destination_exchange').value; // الجهة المختارة
                            const sentAmountInput = document.getElementById('sent_amount_exchange');
                            const balanceErrorDiv = document.getElementById('balanceError');

                            balanceErrorDiv.style.display = 'none';

                            fetch("{{ route('exchange.getBalance') }}", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        currency: currency,
                                        destination_exchange: destinationId
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.error) {
                                        balanceErrorDiv.style.display = 'block';
                                        sentAmountInput.value = '';
                                    } else {
                                        sentAmountInput.value = data.balance;
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                });
                        });
                    </script>

                    <!-- دوال حساب سعر الصرف والمبلغ المستلم -->
                    <script>
                        function getExchangeRate(sentCurrency, receivedCurrency) {
                            const exchangeRates = {
                                'USD': {
                                    'EUR': 0.85,
                                    'TRY': 8.0,
                                    'SAR': 3.75,
                                    'SYP': 2512
                                },
                                'EUR': {
                                    'USD': 1.18,
                                    'TRY': 9.5,
                                    'SAR': 4.4,
                                    'SYP': 2950
                                },
                                'SAR': {
                                    'USD': 0.27,
                                    'EUR': 0.23,
                                    'TRY': 2.13,
                                    'SYP': 670
                                },
                                'TRY': {
                                    'USD': 0.12,
                                    'EUR': 0.10,
                                    'SAR': 0.47,
                                    'SYP': 315
                                },
                                'SYP': {
                                    'USD': 0.0004,
                                    'EUR': 0.00034,
                                    'SAR': 0.0015,
                                    'TRY': 0.0032
                                }
                            };

                            return (exchangeRates[sentCurrency] && exchangeRates[sentCurrency][receivedCurrency]) ?
                                exchangeRates[sentCurrency][receivedCurrency] : 1;
                        }
                    </script>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const sentAmountInput = document.getElementById('sent_amount_exchange');
                            const exchangeRateInput = document.getElementById('exchange_rate_exchange');
                            const receivedAmountInput = document.getElementById('received_amount_exchange');
                            const sentCurrencySelect = document.getElementById('sent_currency_exchange');
                            const receivedCurrencySelect = document.getElementById('received_currency_exchange');

                            // تحديد العملات الأضعف لكل عملة لتحديد عملية الضرب أو القسمة
                            const weakerCurrencies = {
                                'USD': ['TRY', 'SYP', 'SAR'],
                                'EUR': ['USD', 'TRY', 'SYP', 'SAR'],
                                'SAR': ['TRY', 'SYP'],
                                'TRY': ['SYP'],
                                'SYP': []
                            };

                            function isWeaker(sentCurrency, receivedCurrency) {
                                return weakerCurrencies[receivedCurrency]?.includes(sentCurrency);
                            }

                            function updateReceivedAmount() {
                                const sentAmount = parseFloat(sentAmountInput.value) || 0;
                                const exchangeRate = parseFloat(exchangeRateInput.value) || 0;
                                const sentCurrency = sentCurrencySelect.value;
                                const receivedCurrency = receivedCurrencySelect.value;
                                let receivedAmount = 0;
                                if (sentCurrency && receivedCurrency) {
                                    if (isWeaker(sentCurrency, receivedCurrency)) {
                                        receivedAmount = sentAmount / exchangeRate;
                                    } else {
                                        receivedAmount = sentAmount * exchangeRate;
                                    }
                                }
                                receivedAmountInput.value = receivedAmount.toFixed(2);
                            }

                            function updateExchangeRate() {
                                const sentCurrency = sentCurrencySelect.value;
                                const receivedCurrency = receivedCurrencySelect.value;
                                const exchangeRate = getExchangeRate(sentCurrency, receivedCurrency);
                                exchangeRateInput.value = exchangeRate;
                            }

                            sentAmountInput.addEventListener('input', updateReceivedAmount);
                            exchangeRateInput.addEventListener('input', updateReceivedAmount);
                            sentCurrencySelect.addEventListener('change', function() {
                                updateExchangeRate();
                                updateReceivedAmount();
                            });
                            receivedCurrencySelect.addEventListener('change', function() {
                                updateExchangeRate();
                                updateReceivedAmount();
                            });

                            // التهيئة الأولية
                            updateExchangeRate();
                            updateReceivedAmount();
                        });
                    </script>

                    <!-- عرض نافذة SweetAlert عند نجاح الحوالة -->
                    @if (session('exchange'))
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                // استخراج بيانات الحوالة من الجلسة
                                const sentCurrency = "{{ session('exchange')['sent_currency'] }}";
                                const receivedCurrency = "{{ session('exchange')['received_currency'] }}";
                                const sentAmount = "{{ session('exchange')['sent_amount'] }}";
                                const receivedAmount = "{{ session('exchange')['received_amount'] }}";

                                const sentCurrencySelect = document.getElementById('sent_currency_exchange');
                                const receivedCurrencySelect = document.getElementById('received_currency_exchange');
                                const destinationSelect = document.getElementById('destination_exchange');

                                const sentCurrencyOption = sentCurrencySelect?.querySelector(`option[value="${sentCurrency}"]`);
                                const receivedCurrencyOption = receivedCurrencySelect?.querySelector(
                                    `option[value="${receivedCurrency}"]`);
                                const destinationOption = destinationSelect?.querySelector(
                                    `option[value="{{ session('exchange')['destination_exchange'] }}"]`);

                                const sentCurrencyNameAr = sentCurrencyOption ? sentCurrencyOption.textContent.split(' - ')[0] :
                                    sentCurrency;
                                const receivedCurrencyNameAr = receivedCurrencyOption ? receivedCurrencyOption.textContent.split(' - ')[
                                    0] : receivedCurrency;
                                const destinationData = destinationOption ? destinationOption.textContent.split(' - ') : [];
                                const officeName = destinationData[0] || "غير محدد";
                                const state = destinationData[1] || "";
                                const country = destinationData[2] ? destinationData[2].trim().split(' ')[0] : "";

                                Swal.fire({
                                    title: 'تم إرسال  بنجاح',
                                    html: `
                                        <div class="font-cairo max-w-full mx-auto" style="direction: rtl;">
                                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 mb-3 p-2 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl shadow-lg">
                                                <div class="text-center p-3 bg-white rounded-lg shadow-md transform hover:scale-105 transition duration-100">
                                                    <i class="fas fa-university text-blue-500  mb-1"></i>
                                                    <strong class="block text-gray-800 ">الجهة</strong>
                                                    <p class="text-gray-600 ">${officeName}</p>
                                                    <p class="text-gray-600 ">${state} - ${country}</p>
                                                </div>
                                                <div class="text-center p-3 bg-white rounded-lg shadow-md transform hover:scale-105 transition duration-100">
                                                    <i class="fas fa-coins text-green-500  mb-1"></i>
                                                    <strong class="block text-gray-800 ">بيع مبلغ</strong>
                                                    <p class="text-gray-600 ">${sentAmount} ${sentCurrencyNameAr}</p>
                                                </div>
                                                <div class="text-center p-3 bg-white rounded-lg shadow-md transform hover:scale-105 transition duration-100">
                                                    <i class="fas fa-wallet text-yellow-500  mb-1"></i>
                                                    <strong class="block text-gray-800 ">شراء مبلغ</strong>
                                                    <p class="text-gray-600 ">${receivedAmount} ${receivedCurrencyNameAr}</p>
                                                </div>
                                                <div class="text-center p-3 bg-white rounded-lg shadow-md transform hover:scale-105 transition duration-100">
                                                    <i class="fas fa-exchange-alt text-purple-500  mb-1"></i>
                                                    <strong class="block text-gray-800 ">سعر الصرف</strong>
                                                    <p class="text-gray-600 ">{{ session('exchange')['exchange_rate'] }}</p>
                                                </div>
                                            </div>
                                            <div class="text-center p-2 bg-yellow-50 rounded-lg shadow-sm">
                                                <i class="fas fa-sticky-note text-yellow-600  mb-1"></i>
                                                <strong class="block text-gray-800 ">ملاحظة</strong>
                                                <p class="text-gray-600 ">{{ session('exchange')['note'] ?? 'لا يوجد' }}</p>
                                            </div>
                                        </div>
                                    `,
                                    confirmButtonText: 'حسنًا',
                                    confirmButtonColor: '#2563eb',
                                    width: '60%',
                                    customClass: {
                                        popup: 'rounded-2xl shadow-2xl',
                                        title: 'text-lg font-bold text-gray-900',
                                        confirmButton: 'px-3 py-1 text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition duration-300'
                                    }
                                });
                            });
                        </script>
                    @endif


                </div>

                <!-- Approval Tab Content -->
                <div class="tab-pane fade" id="pills-approval" role="tabpanel" aria-labelledby="pills-approval-tab">
                    <x-TransferFormapproval :currencies="$currencies" :destinations="$destinations" />
                </div>

                <div class="tab-pane fade" id="pills-SYP" role="tabpanel" aria-labelledby="pills-SYP-tab">
                    <x-transfer-form-syp :destinations="$destinations" :currencies="$currencies" :exchangeRate="$exchangeRate" />

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
