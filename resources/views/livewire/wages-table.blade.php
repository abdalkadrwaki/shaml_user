<div>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="text-center card-header font-weight-bold">
                        <h5>نموذج إرسال الحوالة</h5>
                        
                    </div>
                    <div class="card-body">
                        <div class="p-4 rounded shadow-sm bg-custom-gray">

                            <div class="mb-4">
                                <label for="currency" class="form-label">اختار العملة</label>
                                <select wire:model="selectedCurrency" id="currency" class="form-select">
                                    <option value="">جميع العملات</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name_ar }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- عرض البيانات في الجدول -->
                            <table
                                class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md table-bordered table-striped"
                                style="direction: rtl;">
                                <thead class="text-white bg-bak-gray">
                                    <tr class="text-center">
                                        <th class="px-4 py-2 border-b">من المبلغ</th>
                                        <th class="px-4 py-2 border-b">إلى المبلغ</th>
                                        <th class="px-4 py-2 border-b">الرسوم</th>
                                        <th class="px-4 py-2 border-b">العملة</th>
                                        <th class="px-4 py-2 border-b">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($wages as $wage)
                                        <tr class="text-sm text-center text-gray-500 hover:bg-gray-100">
                                            <!-- عرض الحقول بشكل عادي أو حقول إدخال إذا كان المستخدم في وضع التعديل -->
                                            <td class="px-4 py-2 text-center border-b">
                                                @if ($editWageId == $wage->id)
                                                    <input type="text" wire:model="fromAmount" class="form-control number-only">
                                                @else
                                                    {{ $wage->from_amount }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-center border-b">
                                                @if ($editWageId == $wage->id)
                                                    <input type="text" wire:model="toAmount" class="form-control number-only">
                                                @else
                                                    {{ $wage->to_amount }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-center border-b">
                                                @if ($editWageId == $wage->id)
                                                    <input type="text" wire:model="fee" class="form-control number-only">
                                                @else
                                                    {{ $wage->fee }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-center border-b">
                                                @if ($editWageId == $wage->id)
                                                    <select wire:model="currency_id" class="form-select">
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}">{{ $currency->name_ar }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    {{ $wage->currency->name_ar ?? 'غير متوفر' }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-center border-b">
                                                @if ($editWageId == $wage->id)
                                                    <!-- زر تحديث السجل -->
                                                    <button wire:click="update" class="btn btn-success">تحديث</button>
                                                    <button wire:click="resetEditFields"
                                                        class="btn btn-secondary">إلغاء</button>
                                                @else
                                                    <!-- زر التعديل -->
                                                    <button wire:click="edit({{ $wage->id }})"
                                                        class="btn btn-primary">تعديل</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
