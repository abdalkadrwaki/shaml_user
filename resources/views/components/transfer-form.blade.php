<div class="p-4 bg-custom-gray2 shadow-md rounded-md ">
    <form id="transfer-form" method="POST" action="{{ route('dashboard.transfer.submit') }}">
        @csrf

        <!-- بيانات المستفيد -->
        <div class="mb-3 row">
            <div class="col-md-6">
                <label for="recipient_name_transfer" class="form-label">اسم المستفيد</label>
                <input type="text" id="recipient_name_transfer" name="recipient_name"
                    class="form-control rounded-md  border-gray-300" value="{{ old('recipient_name') }}"
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
                    value="{{ old('recipient_mobile') }}" required>
                @error('recipient_mobile')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <span id="recipient_mobile_error" class="text-danger roun"></span>
            </div>
        </div>

        <!-- اختيار الجهة -->
        <div class="mb-3 row">
            <div  class="col-md-12" >
                <label for="destination_transfer" class="form-label">الجهة</label>
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
                    class="form-control number-only  rounded-md  border-gray-300" value="{{ old('sent_amount') }}"
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
                <label for="fees_transfer" class="form-label">الأجور</label>
                <input type="text" id="fees_transfer" name="fees"
                    class="form-control number-only  rounded-md  border-gray-300" value="{{ old('fees') }}"
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
    (function(){
        // تعريف متغيرات خاصة بالكود الأول
        let transferData = {};
        let receiptImage = '';
        let globalMovementNumber = '';

        document.getElementById('transfer-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');

            // تعطيل الزر أثناء المعالجة
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
                if (data.error) throw new Error(data.error);

                // حفظ بيانات الحوالة
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

                receiptImage = data.receipt_image; // تم تغيير الاسم من image_data
                globalMovementNumber = data.movement_number;

                showImageModal(receiptImage);

            } catch (error) {
                alert(`❌ خطأ: ${error.message}`);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '📩 إرسال الحوالة';
            }
        });

        // دالة عرض نافذة الإيصال المنبثقة
        function showImageModal(imageData) {
            const modal = document.createElement('div');
            modal.innerHTML = `
                <div id="imageModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-90">
                    <div class="relative w-full max-w-3xl p-6 bg-white rounded-lg shadow-2xl">
                        <div class="overflow-hidden border-4 border-blue-900 rounded-lg shadow-lg">
                            <img src="data:image/png;base64,${imageData}" alt="إيصال الحوالة" class="w-full h-auto">
                        </div>
                        <div class="flex justify-between w-full mt-6 space-x-4">
                            <button onclick="firstTransfer.copyData()" class="btn-blue">📋 نسخ البيانات</button>
                            <button onclick="firstTransfer.downloadImage()" class="btn-green">📥 تنزيل الصورة</button>
                            <button onclick="firstTransfer.closeModal()" class="btn-red">❌ إغلاق</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // دالة إغلاق النافذة المنبثقة
        function closeModal() {
            const modal = document.getElementById('imageModal');
            if (modal) modal.remove();
            document.getElementById('transfer-form').reset();
        }

        // دالة نسخ البيانات
        function copyData() {
            if (!transferData.movementNumber) {
                alert('⚠️ لا توجد بيانات متاحة للنسخ!');
                return;
            }
            const data = `
     *  شركة الشامل  *
     ━━━━━━━━━━━━━━━━━━━━━━
     * رقم الإشعار: ${transferData.movementNumber}
     * كلمة السر: ${transferData.password}
     ━━━━━━━━━━━━━━━━━━━━━━
     * اسم المستفيد: ${transferData.recipientName}
     - ${transferData.destination}
     ━━━━━━━━━━━━━━━━━━━━━━
     * المبلغ المستلم: ${transferData.sentAmount} ${transferData.sent_currency}
     ━━━━━━━━━━━━━━━━━━━━━━
     * الوجهة: ${transferData.Office_name}
     ━━━━━━━━━━━━━━━━━━━━━━
     ${transferData.user_address}
     ━━━━━━━━━━━━━━━━━━━━━━
     * الملاحظة: ${transferData.note}
     ━━━━━━━━━━━━━━━━━━━━━━`;
            navigator.clipboard.writeText(data)
                .then(() => alert('✅ تم نسخ البيانات بنجاح!'))
                .catch(() => alert('❌ فشل في النسخ!'));
        }

        // دالة تنزيل الصورة
        function downloadImage() {
            if (!receiptImage) {
                alert('⚠️ لا توجد صورة متاحة!');
                return;
            }
            const link = document.createElement('a');
            link.href = `data:image/png;base64,${receiptImage}`;
            link.download = `${globalMovementNumber || 'receipt'}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // إضافة أنماط الأزرار
        const style = document.createElement('style');
        style.innerHTML = `
            .btn-blue {
                background: linear-gradient(to right, #3b82f6, #1d4ed8);
                color: white; padding: 10px 20px;
                border-radius: 8px; border: none;
                font-size: 16px; cursor: pointer;
                transition: transform 0.2s;
            }
            .btn-green {
                background: linear-gradient(to right, #10b981, #047857);
                color: white; padding: 10px 20px;
                border-radius: 8px; border: none;
                font-size: 16px; cursor: pointer;
                transition: transform 0.2s;
            }
            .btn-red {
                background: linear-gradient(to right, #ef4444, #b91c1c);
                color: white; padding: 10px 20px;
                border-radius: 8px; border: none;
                font-size: 16px; cursor: pointer;
                transition: transform 0.2s;
            }
            .btn-blue:hover, .btn-green:hover, .btn-red:hover {
                transform: scale(1.05);
            }
        `;
        document.head.appendChild(style);

        // تصدير الدوال المطلوبة ضمن كائن firstTransfer
        window.firstTransfer = {
            copyData: copyData,
            downloadImage: downloadImage,
            closeModal: closeModal
        };
    })();
    </script>
