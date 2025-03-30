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




    <div>
        <div class="flex gap-4 justify-between mt-4">
            @foreach ($balances as $key => $balanceData)
                @php
                    $currency = $balanceData['currency'];
                    $balance = $balanceData['balance'];
                    $balanceStatus = $balance > 0 ? 'دائن لكم' : ($balance < 0 ? 'دائن عليكم' : '');
                    $textColor = $balanceStatus === 'دائن عليكم' ? 'text-red-500' : 'text-green-500';
                    $formattedBalance = number_format(abs($balance), 2);
                @endphp

                @if ($balance != 0)
                    <a href="{{ route('transfers.index', [
                        'currency' => $currency->name_en,
                        'from_date' => request('from_date', now()->format('Y-m-d')),
                        'to_date' => request('to_date', now()->format('Y-m-d')),
                    ]) }}"
                        class="bg-white shadow-md rounded-md flex flex-col items-center text-center flex-1 mx-2 no-underline hover:no-underline">
                        <div class="w-full bg-blue-900 py-2 rounded-t-md">
                            <h2 class="text-xl font-bold text-white">{{ $currency->name_ar }}</h2>
                        </div>
                        <div class="w-full bg-custom-gray2 py-2 rounded-t-md border-b border-blue-900">
                            <h2 class="text-xl font-bold {{ $textColor }}">{{ $balanceStatus }}</h2>
                        </div>
                        <div class="w-auto p-1 m-2 rounded-md">
                            <p class="text-2xl mt-2 {{ $textColor }}">
                                @if ($balance < 0)
                                    -{{ $formattedBalance }}
                                @else
                                    {{ $formattedBalance }}
                                @endif
                            </p>
                        </div>
                    </a>
                @endif
            @endforeach

            {{-- بطاقة رصيد الدولار --}}
            @if(isset($balance_in_usd_))
                @php
                    $usdTextStatus = $balance_in_usd_ > 0 ? 'دائن لكم' : ($balance_in_usd_ < 0 ? 'دائن عليكم' : '');
                    $usdTextColor = $balance_in_usd_ < 0 ? 'text-red-500' : 'text-green-500';
                    $formattedUSD = number_format(abs($balance_in_usd_), 2);
                @endphp
                <a href="{{ route('transfers.index', [
                    'currency' => 'usd',
                    'from_date' => request('from_date', now()->format('Y-m-d')),
                    'to_date' => request('to_date', now()->format('Y-m-d')),
                ]) }}"
                    class="bg-white shadow-md rounded-md flex flex-col items-center text-center flex-1 mx-2 no-underline hover:no-underline">
                    <div class="w-full bg-blue-900 py-2 rounded-t-md">
                        <h2 class="text-xl font-bold text-white">ميزان</h2>
                    </div>
                    <div class="w-full bg-custom-gray2 py-2 rounded-t-md border-b border-blue-900">
                        <h2 class="text-xl font-bold {{ $usdTextColor }}">{{ $usdTextStatus }}</h2>
                    </div>
                    <div class="w-auto p-1 m-2 rounded-md">
                        <p class="text-2xl mt-2 {{ $usdTextColor }}">
                            @if ($balance_in_usd_ < 0)
                                -{{ $formattedUSD }}
                            @else
                                {{ $formattedUSD }}
                            @endif
                        </p>
                    </div>
                </a>
            @endif
        </div>
    </div>


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
                    <div class="p-4 bg-custom-gray2 shadow-md rounded-md ">
    <form id="transfer-form" method="POST" action="{{ route('dashboard.transfer.submit') }}">
        @csrf

        <!-- بيانات المستفيد -->
        <div class="mb-3 row">
            <div class="col-md-6">
                <label for="recipient_name_transfer" class="form-label">اسم المستفيد</label>
                <input type="text" id="recipient_name_transfer" name="recipient_name"
                    class="form-control rounded-md  border-gray-300" value="{{ old('recipient_name', 'محمد') }}"
                    required>
                @error('recipient_name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="recipient_name_error" class="text-danger"></span>
            </div>
            <div class="col-md-6">
                <label for="recipient_mobile_transfer" class="form-label ">جوال المستفيد</label>
                <input type="tel" id="recipient_mobile_transfer" name="recipient_mobile"
                    class="form-control  rounded-md  border-gray-300"
                    value="{{ old('recipient_mobile', '0596123781') }}" required>
                @error('recipient_mobile')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="recipient_mobile_error" class="text-danger roun"></span>
            </div>
        </div>

        <!-- اختيار الجهة -->
        <div class="mb-3 row">
            <div  class="col-md-12" >
                <label for="destination" class="form-label">الجهة</label>
                <select id="destination_transfer" name="destination"
                    class="form-select js-example-basic-single rounded-md  border-gray-300" required>
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

        <!-- العملة والمبالغ -->
        <div class="mb-3 row">
            <div class="col-md-6">
                <label for="sent_currency_transfer" class="form-label">العملة المرسلة</label>
                <select id="sent_currency_transfer" name="sent_currency" class="form-select rounded-md  border-gray-300"
                    required>
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
                <span id="sent_currency_error" class="text-danger"></span>
            </div>
            <div class="col-md-6">
                <label for="sent_amount_transfer" class="form-label">المبلغ المرسل</label>
                <input type="text" id="sent_amount_transfer" name="sent_amount"
                    class="form-control number-only  rounded-md  border-gray-300" value="{{ old('sent_amount', 1) }}"
                    step="0.01" required>
                @error('sent_amount')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="sent_amount_error" class="text-danger"></span>
            </div>
        </div>

        <div class="mb-3 row">
            <div class="col-md-6 ">
                <label for="received_currency_transfer" class="form-label">العملة المستلمة</label>
                <select id="received_currency_transfer" name="received_currency"
                    class="form-select  rounded-md  border-gray-300" required>
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
                <span id="received_currency_error" class="text-danger"></span>
            </div>
            <div class="col-md-6">
                <label for="received_amount_transfer" class="form-label">المبلغ المستلم</label>
                <input type="text" id="received_amount_transfer" name="received_amount"
                    class="form-control number-only bg-gray-200  rounded-md  border-gray-300" value="" readonly>
                @error('received_amount')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="received_amount_error" class="text-danger"></span>
            </div>
        </div>

        <!-- الأجور وسعر الصرف -->
        <div class="mb-3 row">
            <div class="col-md-6">
                <label for="fees_transfer_transfer" class="form-label">الأجور</label>
                <input type="text" id="fees_transfer" name="fees"
                    class="form-control number-only  rounded-md  border-gray-300" value="{{ old('fees', 1) }}"
                    step="0.01">
                @error('fees')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="fees_error" class="text-danger"></span>
            </div>
            <div class="col-md-6">
                <label for="exchange_rate" class="form-label">الصرف</label>
                <input type="text" id="exchange_rate" name="exchange_rate"
                    class="form-control number-only  rounded-md  border-gray-300"
                    value="{{ old('exchange_rate') }}">
                @error('exchange_rate')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="exchange_rate_error" class="text-danger"></span>
            </div>
        </div>

        <!-- الملاحظة -->
        <div class="mb-3 row">
            <div class="col-md-12">
                <label for="note_transfer" class="form-label">ملاحظة</label>
                <textarea id="note_transfer" name="note" class="form-control  rounded-md  border-gray-300">{{ old('note') }}</textarea>
                @error('note')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="note_error" class="text-danger"></span>
            </div>
        </div>

        <!-- عنوان الجهة (مخفي افتراضيًا) -->
        <div class="mb-3 row" id="destination_address_container" style="display:none;">
            <div class="col-md-12">
                <label for="destination_address" class="form-label  rounded-md  border-gray-300">عنوان الجهة</label>
                <p id="destination_address" class="form-control"></p>
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


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // العناصر الأساسية
        const sentAmountInput = document.getElementById('sent_amount_transfer');
        const exchangeRateInput = document.getElementById('exchange_rate');
        const receivedAmountInput = document.getElementById('received_amount_transfer');
        const sentCurrencySelect = document.getElementById('sent_currency_transfer');
        const receivedCurrencySelect = document.getElementById('received_currency_transfer');

        // أسعار الصرف الثابتة (يمكن استبدالها ببيانات من API)
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

        // تحديد العملات الأضعف
        const weakerCurrencies = {
            'USD': ['SYP'],
            'EUR': ['SYP'],
            'SAR': ['SYP'],
            'TRY': ['SYP'],
            'SYP': []
        };

        // الحصول على سعر الصرف
        function getExchangeRate(sentCurrency, receivedCurrency) {
            return exchangeRates[sentCurrency]?.[receivedCurrency] || 1;
        }

        // تحديد إذا كانت العملة المستلمة أضعف
        function isWeaker(sentCurrency, receivedCurrency) {
            return weakerCurrencies[sentCurrency]?.includes(receivedCurrency);
        }

        // تحديث المبلغ المستلم
        function updateReceivedAmount() {
            const sentAmount = parseFloat(sentAmountInput.value) || 0;
            const exchangeRate = parseFloat(exchangeRateInput.value) || 1;
            const sentCurrency = sentCurrencySelect.value;
            const receivedCurrency = receivedCurrencySelect.value;

            let receivedAmount = 0;

            if (isWeaker(sentCurrency, receivedCurrency)) {
                receivedAmount = sentAmount * exchangeRate;
            } else {
                receivedAmount = sentAmount / exchangeRate;
            }

            receivedAmountInput.value = receivedAmount.toFixed(0);
        }

        // تحديث سعر الصرف تلقائيًا
        function updateExchangeRate() {
            const sentCurrency = sentCurrencySelect.value;
            const receivedCurrency = receivedCurrencySelect.value;
            const rate = getExchangeRate(sentCurrency, receivedCurrency);
            exchangeRateInput.value = rate.toFixed(0);
        }

        // التحقق من المدخلات الرقمية
        function validateNumberInput(e) {
            e.target.value = e.target.value.replace(/[^0-9.]/g, '');
            e.target.value = e.target.value.replace(/(\..*)\./g, '$1');
        }

        // الأحداث
        sentAmountInput.addEventListener('input', (e) => {
            validateNumberInput(e);
            updateReceivedAmount();
        });

        exchangeRateInput.addEventListener('input', (e) => {
            validateNumberInput(e);
            updateReceivedAmount();
        });

        sentCurrencySelect.addEventListener('change', () => {
            updateExchangeRate();
            updateReceivedAmount();
        });

        receivedCurrencySelect.addEventListener('change', () => {
            updateExchangeRate();
            updateReceivedAmount();
        });

        // التهيئة الأولية
        updateExchangeRate();
        updateReceivedAmount();
    });
</script>
<script>
   // تعريف متغيرات عامة لتخزين بيانات الحوالة المسترجعة من قاعدة البيانات
let transferData = {};
let globalImageData = '';
let globalMovementNumber = '';

document.getElementById('transfer-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '🔄 جاري الإرسال...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        // حفظ بيانات الحوالة المسترجعة من قاعدة البيانات
        transferData = {
            movementNumber: data.movement_number,
            recipientName: data.recipient_name,
            recipientMobile: data.recipient_mobile,
            destination: data.destination,
            sentAmount: data.sent_amount,
            sent_currency: data.sent_currency,
            password: data.password,
            Office_name: data.Office_name,
            user_address: data.user_address,
            note: data.note || 'لا توجد ملاحظات'
        };

        // تخزين بيانات الصورة
        globalImageData = data.image_data;
        globalMovementNumber = data.movement_number;

        // عرض الصورة في نافذة منبثقة
        showImageModal(globalImageData);

    } catch (error) {
        alert(`❌ خطأ: ${error.message}`);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '📩 إرسال الحوالة';
    }
});

// دالة عرض صورة الإيصال
function showImageModal(imageData) {
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div id="imageModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-90">
            <div class="relative w-full max-w-3xl p-6 bg-white rounded-lg shadow-2xl">
                <div class="overflow-hidden border-4 border-blue-900 rounded-lg shadow-lg">
                    <img src="data:image/png;base64,${imageData}" alt="إيصال الحوالة" class="w-full h-auto">
                </div>
                <div class="flex justify-between w-full mt-6 space-x-4">
                    <button onclick="copyData()" class="btn-blue">📋 نسخ البيانات</button>
                    <button onclick="downloadImage()" class="btn-green">📥 تنزيل الصورة</button>
                    <button onclick="closeModal()" class="btn-red">❌ إغلاق</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// دالة إغلاق النافذة المنبثقة
function closeModal() {
    // إزالة المودال من الصفحة
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.remove();
    }

    // تصفير بيانات الفورم
    const form = document.getElementById('transfer-form');
    if (form) {
        form.reset(); // تصفير جميع الحقول في الفورم
    }


}


// دالة نسخ البيانات بتنسيق احترافي
function copyData() {
    if (Object.keys(transferData).length === 0) {
        alert('⚠️ لا توجد بيانات متاحة للنسخ!');
        return;
    }

    const data = `
 *  شركة الشامل  *
━━━━━━━━━━━━━━━━━━━━━━
 *رقم الإشعار:*  ${transferData.movementNumber}
 *كلمة السر:*  ${transferData.password}
━━━━━━━━━━━━━━━━━━━━━━
 *اسم المستفيد:*  ${transferData.recipientName}
- ${transferData.destination}
━━━━━━━━━━━━━━━━━━━━━━
 *المبلغ المستلم:*  ${transferData.sentAmount} ${transferData.sent_currency}
━━━━━━━━━━━━━━━━━━━━━━
* الوجهه*
${transferData.Office_name}
━━━━━━━━━━━━━━━━━━━━━━
${transferData.user_address}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *الملاحظة:*  ${transferData.note}
━━━━━━━━━━━━━━━━━━

    `;

    navigator.clipboard.writeText(data)
        .then(() => {
            alert('✅ تم نسخ جميع بيانات الحوالة بنجاح!');
        })
        .catch(() => {
            alert('❌ حدث خطأ أثناء نسخ البيانات.');
        });
}

// دالة تحميل صورة الإيصال
function downloadImage() {
    if (!globalImageData) {
        alert('⚠️ لا توجد صورة متاحة للتنزيل!');
        return;
    }
    const link = document.createElement('a');
    let fileName = globalMovementNumber ? `${globalMovementNumber}.png` : 'transfer_receipt.png';
    link.href = `data:image/png;base64,${globalImageData}`;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// تحسين الأزرار بتنسيق أنيق
const style = document.createElement('style');
style.innerHTML = `
    .btn-blue {
        background: linear-gradient(to right, #3b82f6, #1d4ed8);
        color: white; padding: 10px 20px;
        border-radius: 8px; border: none;
        font-size: 16px; cursor: pointer;
        transition: transform 0.2s ease-in-out;
    }
    .btn-green {
        background: linear-gradient(to right, #10b981, #047857);
        color: white; padding: 10px 20px;
        border-radius: 8px; border: none;
        font-size: 16px; cursor: pointer;
        transition: transform 0.2s ease-in-out;
    }
    .btn-red {
        background: linear-gradient(to right, #ef4444, #b91c1c);
        color: white; padding: 10px 20px;
        border-radius: 8px; border: none;
        font-size: 16px; cursor: pointer;
        transition: transform 0.2s ease-in-out;
    }
    .btn-blue:hover, .btn-green:hover, .btn-red:hover {
        transform: scale(1.05);
    }
`;
document.head.appendChild(style);

</script>

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

                </div>

                <div class="tab-pane fade" id="pills-SYP" role="tabpanel" aria-labelledby="pills-SYP-tab">
                    <div class="p-4 bg-custom-gray2 shadow-md rounded-md ">
                        <form id="transfer-form_syp" method="POST" action="{{ route('syp.submit') }}">
                            @csrf

                            <!-- بيانات المستفيد -->
                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label for="recipient_name_syp" class="form-label">اسم المستفيد</label>
                                    <input type="text" id="recipient_name_syp" name="recipient_name"
                                        class="form-control rounded-md  border-gray-300" value="{{ old('recipient_name', 'محمد') }}"
                                        required>
                                    @error('recipient_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <span id="recipient_name_error" class="text-danger"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="recipient_mobile_syp" class="form-label ">جوال المستفيد</label>
                                    <input type="tel" id="recipient_mobile_syp" name="recipient_mobile"
                                        class="form-control  rounded-md  border-gray-300"
                                        value="{{ old('recipient_mobile', '0596123781') }}" required>
                                    @error('recipient_mobile')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <span id="recipient_mobile_error" class="text-danger roun"></span>
                                </div>
                            </div>

                            <!-- اختيار الجهة -->
                            <div class="mb-3 row">
                                <div class="col-md-12">
                                    <label for="destination" class="form-label">الجهة</label>
                                    <select id="destination_syp" name="destination"
                                        class="form-select js-example-basic-single rounded-md  border-gray-300" required>
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

                            <!-- العملة والمبالغ -->
                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label for="sent_currency_syp" class="form-label">العملة المرسلة</label>
                                    <select id="sent_currency_syp" name="sent_currency" class="form-select rounded-md  border-gray-300"
                                        required>
                                        <option value="USD" >دولار</option>
                                            <option value="USD" >دولار</option>

                                    </select>
                                    @error('sent_currency')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <span id="sent_currency_error" class="text-danger"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="sent_amount_syp" class="form-label">المبلغ المرسل</label>
                                    <input type="text" id="sent_amount_syp" name="sent_amount"
                                        class="form-control number-only  rounded-md  border-gray-300" value="{{ old('sent_amount', 1) }}"
                                        step="0.01" required>
                                    @error('sent_amount')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <span id="sent_amount_error" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="col-md-12">

                                    <label for="received_amount_syp" class="form-label">المبلغ المستلم</label>
                                    <input type="text" id="received_amount_syp" name="received_amount"
                                        class="form-control number-only  rounded-md  border-gray-300 received_amount_syp" value="">
                                    @error('received_amount')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <span id="received_amount_error" class="text-danger"></span>

                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label for="received_currency_syp" class="form-label">العملة المستلمة</label>
                                    <select id="received_currency_syp" name="received_currency"
                                        class="form-select  rounded-md  border-gray-300" required>
                                        <option value="SYP" >سوري</option>
                                    </select>
                                    @error('received_currency')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <span id="received_currency_error" class="text-danger"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="received_amount_syp" class="form-label">المبلغ المستلم</label>
                                    <input type="text" id="received_amount_syp" name="received_amount"
                                        class="form-control number-only bg-gray-200  rounded-md  border-gray-300 received_amount_syp" value="" readonly>
                                    @error('received_amount')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <span id="received_amount_error" class="text-danger"></span>
                                </div>
                            </div>

                            <!-- الأجور وسعر الصرف -->
                            <div class="mb-3 row">
                                <div class="col-md-6">
                                    <label for="fees_syp_syp" class="form-label">الأجور</label>
                                    <input type="text" id="fees_syp" name="fees"
                                        class="form-control number-only  rounded-md  border-gray-300" value="{{ old('fees', 1) }}"
                                        step="0.01">
                                    @error('fees')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <span id="fees_error" class="text-danger"></span>
                                </div>
                                <div class="col-md-6" id="destination_address_container1">
                                    <label for="exchange_rate_syp" class="form-label">الصرف</label>
                                    <input type="text" id="exchange_rate_syp" name="exchange_rate" class="form-control number-only rounded-md border-gray-300"
                                           value="{{ old('exchange_rate') }}">
                                    @error('exchange_rate')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <span id="exchange_rate_error" class="text-danger"></span>
                                </div>

                            </div>

                            <!-- الملاحظة -->
                            <div class="mb-3 row">
                                <div class="col-md-12">
                                    <label for="note_syp" class="form-label">ملاحظة</label>
                                    <textarea id="note_syp" name="note" class="form-control  rounded-md  border-gray-300">{{ old('note') }}</textarea>
                                    @error('note')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <span id="note_error" class="text-danger"></span>
                                </div>
                            </div>

                            <!-- عنوان الجهة (مخفي افتراضيًا) -->
                            <div class="mb-3 row" id="destination_address_container_syp" style="display:none;">
                                <div class="col-md-12">
                                    <label for="destination_address" class="form-label  rounded-md  border-gray-300">عنوان الجهة</label>
                                    <p id="destination_address_syp" class="form-control"></p>
                                </div>
                            </div>

                            <!-- زر الإرسال -->
                            <div class="row">
                                <div class="text-center col-md-12">
                                   <div class="position-relative">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="submit-text">إرسال</span>
                                            <div class="spinner-border d-none" id="loading-spinner"></div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>

                    <!-- تضمين SweetAlert2 من CDN -->
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const sentAmountInput = document.getElementById('sent_amount_syp'); // حقل المبلغ المرسل
                            const receivedAmountInput1 = document.querySelector('#received_amount_syp'); // المبلغ المستلم الأول
                            const receivedAmountInput2 = document.querySelectorAll('.received_amount_syp')[1]; // المبلغ المستلم الثاني
                            const exchangeRateInput = document.getElementById('exchange_rate_syp'); // حقل سعر الصرف

                            // تحديث المبلغ المستلم الأول عند تغيير المبلغ المرسل
                            sentAmountInput.addEventListener('input', function() {
                                const exchangeRate = parseFloat(exchangeRateInput.value);
                                if (!isNaN(exchangeRate) && this.value) {
                                    const receivedAmount = (parseFloat(this.value) * exchangeRate).toFixed(2);
                                    receivedAmountInput1.value = receivedAmount;
                                    receivedAmountInput2.value = receivedAmount; // تحديث المبلغ المستلم الثاني
                                } else {
                                    receivedAmountInput1.value = '';
                                    receivedAmountInput2.value = '';
                                }
                            });

                            // تحديث المبلغ المرسل عند تغيير المبلغ المستلم الأول
                            receivedAmountInput1.addEventListener('input', function() {
                                const exchangeRate = parseFloat(exchangeRateInput.value);
                                if (!isNaN(exchangeRate) && this.value && exchangeRate !== 0) {
                                    const sentAmount = (parseFloat(this.value) / exchangeRate).toFixed(2);
                                    sentAmountInput.value = sentAmount;
                                    receivedAmountInput2.value = this.value; // تحديث المبلغ المستلم الثاني بناءً على المبلغ المستلم الأول
                                } else {
                                    sentAmountInput.value = '';
                                    receivedAmountInput2.value = '';
                                }
                            });

                            // تحديث المبلغ المستلم عند تغيير المبلغ المستلم الثاني
                            receivedAmountInput2.addEventListener('input', function() {
                                const exchangeRate = parseFloat(exchangeRateInput.value);
                                if (!isNaN(exchangeRate) && this.value && exchangeRate !== 0) {
                                    const sentAmount = (parseFloat(this.value) / exchangeRate).toFixed(2);
                                    sentAmountInput.value = sentAmount;
                                    receivedAmountInput1.value = this.value; // تحديث المبلغ المستلم الأول بناءً على المبلغ المستلم الثاني
                                } else {
                                    sentAmountInput.value = '';
                                    receivedAmountInput1.value = '';
                                }
                            });

                            // تحديث المبالغ عند تغيير سعر الصرف
                            exchangeRateInput.addEventListener('input', function() {
                                const exchangeRate = parseFloat(this.value);
                                const sentAmount = parseFloat(sentAmountInput.value);
                                if (!isNaN(exchangeRate) && sentAmount) {
                                    const receivedAmount = (sentAmount * exchangeRate).toFixed(2);
                                    receivedAmountInput1.value = receivedAmount;
                                    receivedAmountInput2.value = receivedAmount; // تحديث المبلغ المستلم الثاني
                                }
                            });
                        });
                    </script>

                    </script>

                    <script>
                        let transferData_syp = {};
                        let globalImageData_syp = '';
                        let globalMovementNumber_syp = '';

                        document.getElementById('transfer-form_syp').addEventListener('submit', async function (e) {
                            e.preventDefault();

                            const form = e.target;
                            const formData = new FormData(form);
                            const submitBtn = form.querySelector('button[type="submit"]');

                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '🔄 جاري الإرسال...';

                            try {
                                const response = await fetch(form.action, {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    }
                                });
                                const data = await response.json();

                                if (data.error) {
                                    throw new Error(data.error);
                                }

                                transferData_syp = {
                                    movementNumber: data.movement_number,
                                    recipientName: data.recipient_name,
                                    recipientMobile: data.recipient_mobile,
                                    destination: data.destination,
                                    sentAmount: data.sent_amount,
                                    sent_currency: data.sent_currency,
                                    password: data.password,
                                    Office_name: data.Office_name,
                                    user_address: data.user_address,
                                    note: data.note || 'لا توجد ملاحظات'
                                };

                                globalImageData_syp = data.image_data;
                                globalMovementNumber_syp = data.movement_number;

                                showImageModal(globalImageData_syp);

                            } catch (error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'خطأ',
                                    text: `❌ خطأ: ${error.message}`
                                });
                            } finally {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '📩 إرسال الحوالة';
                            }
                        });

                        function showImageModal(imageData_syp) {
                            const modal = document.createElement('div');
                            modal.innerHTML = `
                                <div id="imageModal_syp" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-90">
                                    <div class="relative w-full max-w-3xl p-6 bg-white rounded-lg shadow-2xl">
                                        <div class="overflow-hidden border-4 border-blue-900 rounded-lg shadow-lg">
                                            <img src="data:image/png;base64,${imageData_syp}" alt="إيصال الحوالة" class="w-full h-auto">
                                        </div>
                                        <div class="flex justify-between w-full mt-6 space-x-4">
                                            <button onclick="copyData()" class="btn-blue">📋 نسخ البيانات</button>
                                            <button onclick="downloadImage()" class="btn-green">📥 تنزيل الصورة</button>
                                            <button onclick="closeModal()" class="btn-red">❌ إغلاق</button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            document.body.appendChild(modal);
                        }

                        function closeModal() {
                            const modal = document.getElementById('imageModal_syp');
                            if (modal) {
                                modal.remove();
                            }
                            const form = document.getElementById('transfer-form_syp');
                            if (form) {
                                form.reset();
                            }
                        }

                        function copyData() {
                            if (Object.keys(transferData_syp).length === 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'تنبيه',
                                    text: '⚠️ لا توجد بيانات متاحة للنسخ!'
                                });
                                return;
                            }

                            const data = `
                      *  شركة الشامل  *
                     ━━━━━━━━━━━━━━━━━━━━━━
                      *رقم الإشعار:*  ${transferData_syp.movementNumber}
                      *كلمة السر:*  ${transferData_syp.password}
                     ━━━━━━━━━━━━━━━━━━━━━━
                      *اسم المستفيد:*  ${transferData_syp.recipientName}
                     - ${transferData_syp.destination}
                     ━━━━━━━━━━━━━━━━━━━━━━
                      *المبلغ المستلم:*  ${transferData_syp.sentAmount} ${transferData_syp.sent_currency}
                     ━━━━━━━━━━━━━━━━━━━━━━
                     * الوجهه*
                     ${transferData_syp.Office_name}
                     ━━━━━━━━━━━━━━━━━━━━━━
                     ${transferData_syp.user_address}
                     ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                      *الملاحظة:*  ${transferData_syp.note}
                     ━━━━━━━━━━━━━━━━━━
                            `;

                            navigator.clipboard.writeText(data)
                                .then(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'نجاح',
                                        text: '✅ تم نسخ جميع بيانات الحوالة بنجاح!'
                                    });
                                })
                                .catch(() => {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'خطأ',
                                        text: '❌ حدث خطأ أثناء نسخ البيانات.'
                                    });
                                });
                        }

                        function downloadImage() {
                            if (!globalImageData_syp) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'تنبيه',
                                    text: '⚠️ لا توجد صورة متاحة للتنزيل!'
                                });
                                return;
                            }
                            const link = document.createElement('a');
                            let fileName = globalMovementNumber_syp ? `${globalMovementNumber_syp}.png` : 'transfer_receipt.png';
                            link.href = `data:image/png;base64,${globalImageData_syp}`;
                            link.download = fileName;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }
                    </script>

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
        console.warn("لا توجد عناصر بالصفحة تحتوي على الصنف .message");
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
