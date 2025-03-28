<x-teacher-layout>
    <x-slot name="header">
        <!-- يمكن إضافة العنوان هنا إذا كان يحتاج لذلك -->
    </x-slot>

    <!-- الحاوية الرئيسية -->
    <div class="container py-3" style="direction: rtl; text-align: right;">

        <!-- نموذج إرسال الحوالة -->
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="text-center card-header font-weight-bold">
                        <h5>نموذج إرسال الحوالة</h5>
                    </div>
                    <div class="card-body">
                        <div class="p-4 rounded shadow-sm bg-custom-gray">
                            <!-- نموذج الإدخال -->
                            <form action="{{ route('wages.store') }}" method="POST">
                                @csrf
                                <div class="mb-3 text-center row">

                                    <!-- نوع التسعير -->
                                    <div class="mb-2 col-md-2">
                                        <label for="type">نوع التسعير</label>
                                        <select name="type" id="type" class="text-center form-control" required>
                                            <option value="#"> نوع التسعير</option>
                                            <option value="1">أجور ثابتة</option>
                                            <option value="2">نسبة مئوية</option>
                                        </select>
                                    </div>

                                    <!-- نوع العملة -->
                                    <div class="mb-2 col-md-2">
                                        <label for="currency_id">نوع العملة</label>
                                        <select id="currency_id" name="currency_id" class="text-center form-control"
                                            required>
                                            <option value="">اختر العملة</option>
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency['id'] }}">{{ $currency['name_ar'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-2 col-md-2" >
                                        <label for="from_amount">المبلغ من</label>
                                        <input type="text" step="0.01" name="from_amount" id="from_amount"
                                            class="text-center form-control number-only" value="1"  required>
                                    </div>

                                    <!-- المبلغ إلى -->
                                    <div class="mb-3 col-md-3">
                                        <label for="to_amount">المبلغ إلى</label>
                                        <input type="text" step="0.01" name="to_amount" id="to_amount"
                                            class="text-center form-control number-only" required>
                                    </div>

                                    <!-- الأجور -->
                                    <div class="mb-2 col-md-3">
                                        <label for="fee">الأجور</label>
                                        <input type="text" step="0.01" name="fee" id="fee"
                                            class="text-center form-control number-only" required>
                                    </div>
                                </div>

                                <!-- زر الحفظ -->
                                <div class="row">
                                    <div class="text-center col-md-12">
                                        <button type="submit"
                                            class="text-white btn bg-bak-gray w-50 hover:bg-bak-gray2">حفظ</button>
                                    </div>
                                </div>

                                <!-- إرسال معرف المستخدم الثاني -->
                                <input type="hidden" name="user_id_2" value="{{ Crypt::encrypt($destination->id) }}">
                            </form>

                            <!-- عرض الأخطاء إن وجدت -->
                            @if ($errors->any())
                                <div class="mt-3 alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- عرض رسالة النجاح -->
                            @if (session('success'))
                                <div class="mt-3 alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <livewire:wages-table />

</x-teacher-layout>
