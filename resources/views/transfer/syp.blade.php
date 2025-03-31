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
