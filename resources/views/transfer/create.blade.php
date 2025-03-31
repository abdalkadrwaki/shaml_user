
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

                </div>


                <div class="tab-pane fade" id="pills-payment-voucher" role="tabpanel"
                    aria-labelledby="pills-payment-voucher-tab">
                 ء
                </div>

                <!-- Approval Tab Content -->
                <div class="tab-pane fade" id="pills-approval" role="tabpanel" aria-labelledby="pills-approval-tab">

                </div>

                <div class="tab-pane fade" id="pills-SYP" role="tabpanel" aria-labelledby="pills-SYP-tab">
                    @include('Transfer.syp')
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
