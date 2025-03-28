<div class="container mt-4">
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>نجاح!</strong> {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="table-responsive shadow-lg rounded-lg bg-white p-6 m-4">
        <h4 class="mb-4 text-primary font-weight-bold">
            {{ $exchangeRateId ? 'تعديل أسعار الصرف' : 'إضافة سعر صرف جديد' }}
        </h4>
        <form wire:submit.prevent="{{ $exchangeRateId ? 'update' : 'store' }}">
            <div class="mb-3 row">
                <div class="col-md-3">
                    <label for="currency_pair" class="font-weight-semibold">زوج العملات:</label>
                    <input type="text" id="currency_pair" wire:model="currency_pair" class="form-control"
                        placeholder="مثل USD/TRY">
                    @error('currency_pair')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="name_ar" class="font-weight-semibold">الاسم بالعربية:</label>
                    <input type="text" id="name_ar" wire:model="name_ar" class="form-control"
                        placeholder="مثل الدولار الأمريكي/الليرة التركية">
                    @error('name_ar')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="buy_rate" class="font-weight-semibold">سعر الشراء:</label>
                    <input type="text"  id="buy_rate" wire:model="buy_rate" class="form-control number-only "
                        placeholder="أدخل سعر الشراء">
                    @error('buy_rate')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="sell_rate" class="font-weight-semibold">سعر البيع:</label>
                    <input type="text"  id="sell_rate" wire:model="sell_rate" class="form-control number-only "
                        placeholder="أدخل سعر البيع">
                    @error('sell_rate')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

            </div>
            <button
                type="submit" class="btn btn-lg w-full  mt-3">{{ $exchangeRateId ? 'تعديل' : 'إضافة' }}</button>
        </form>


    <div class="row">
        <div class="col-12">
            <div class="table-responsive shadow-lg rounded-lg bg-white p-6 m-4">
                <table class="table table-bordered table-hover table-striped ">
                    <thead class="thead-dark">
                        <tr>
                            <th>زوج العملات</th>
                            <th>الاسم بالعربية</th>
                            <th>سعر الشراء</th>
                            <th>سعر البيع</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($exchangeRates as $rate)
                            <tr>
                                <td>{{ $rate->currency_pair }}</td>
                                <td>{{ $rate->name_ar }}</td>
                                <td>{{ $rate->buy_rate }}</td>
                                <td>{{ $rate->sell_rate }}</td>
                                <td>
                                    <button wire:click="edit({{ $rate->id }})"
                                        class="btn btn-warning btn-sm">تعديل</button>
                                    <button wire:click="delete({{ $rate->id }})"
                                        class="btn btn-danger btn-sm">حذف</button>
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
