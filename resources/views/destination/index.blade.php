<x-app-layout>
    <x-slot name="header">
        <!-- العنوان يمكن أن يكون هنا -->
    </x-slot>

    <div class="container mt-4 " style="width: 98%">
        <div class="card-body">
            <!-- تبويبات التنقل بين الجداول -->
            <ul class="p-1 mb-3  nav nav-pills mt-7 justify-content-center" id="pills-tab" role="tablist"
                style="display: flex; width: 100%; justify-content: space-between;">
                <li class="nav-item" role="presentation"
                    style="flex: 1 1 48%; border-right: 1px solid #ddd; margin-right: 10px;">
                    <a class="px-2 py-2 text-center bg-blue-900 text-white nav-link active hover:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        id="pills-send-request-tab" data-bs-toggle="pill" href="#pills-send-request" role="tab"
                        aria-controls="pills-send-request" aria-selected="true" style="width: 100%;">
                        الارصدة
                    </a>
                </li>
                <li class="nav-item" role="presentation" style="flex: 2 1 48%; margin-left: -5px;">
                    <a class="px-2 py-2 text-center bg-blue-900 text-white nav-link hover:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        id="pills-received-request-tab" data-bs-toggle="pill" href="#pills-received-request"
                        role="tab" aria-controls="pills-received-request" aria-selected="false"
                        style="width: 100%;">
                        الاذونات
                        <span class="badge bg-danger ms-1"></span>
                    </a>
                </li>
            </ul>

            <!-- محتوى التبويبات -->
            <div class="tab-content" id="pills-tabContent">

                <!-- جدول إرسال طلب صداقة -->
                <div class="tab-pane fade show active" id="pills-send-request" role="tabpanel"
                    aria-labelledby="pills-send-request-tab">
                    <div class="mb-4 card">
                        <div class="text-center text-gray-100 bg-blue-500 card-header">
                            <strong class="y"> الارصدة </strong>
                        </div>
                        <div class="container mx-auto mt-3">
                            <table
                                class="w-full overflow-hidden border border-gray-300 rounded-lg shadow-md table-auto tebl"
                                style="direction: rtl;">
                                <thead class="text-center text-gray-700 bg-gray-200">
                                    <tr class="text-center">
                                        <th class="px-3 py-2 text-center border-b">#</th>
                                        <th class="px-3 py-2 text-center border-b">اسم المكتب</th>
                                        <th class="px-3 py-2 text-center border-b">ميزان</th>
                                        @foreach ($columns as $column)
                                            @php
                                                $currencyName = $currencyNames->get(
                                                    str_replace(['_1', '_2'], '', $column['receiver_column']),
                                                );
                                            @endphp
                                            <th class="px-3 py-2 text-center border-b">
                                                {{ $currencyName ?? 'غير متوفر' }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($friendRequests as $index => $request)
                                        @php
                                            // بيانات المكتب
                                            $clientId =
                                                $request->receiver_id === Auth::id()
                                                    ? $request->sender_id
                                                    : $request->receiver_id;
                                            $officeData = $destinations->firstWhere('id', $clientId);

                                            // تحضير بيانات النسخ
                                            $balanceData = [];
                                            foreach ($columns as $column) {
                                                $currencyCode = str_replace(
                                                    ['_1', '_2'],
                                                    '',
                                                    $column['receiver_column'],
                                                );
                                                $columnKey =
                                                    $request->receiver_id === Auth::id()
                                                        ? $column['receiver_column']
                                                        : $column['sender_column'];

                                                $balanceData[$currencyCode] = [
                                                    'value' => $request->{$columnKey} ?? 0,
                                                    'text' => $currencyNames->get($currencyCode) ?? $currencyCode,
                                                ];
                                            }
                                        @endphp
                                        <tr class="text-sm text-center text-gray-500 hover:bg-gray-100">
                                            <td class="px-2 py-2 text-center border-b">{{ $index + 1 }}</td>
                                            <td class="px-3 py-1 font-bold text-center border-b">
                                                <div class="flex flex-col items-center">
                                                    <span>{{ $officeData->Office_name ?? 'غير متوفر' }}</span>
                                                    <span class="text-sm text-gray-600">
                                                        {{ ($officeData->country_user ?? '') . ' - ' . ($officeData->state_user ?? '') }}
                                                    </span>
                                                    <button type="button"
                                                        class="mt-1 text-xs text-blue-600 hover:text-blue-800 copy-all-balances"
                                                        data-office="{{ $officeData->Office_name ?? 'غير متوفر' }}"
                                                        data-location="{{ ($officeData->country_user ?? '') . ' - ' . ($officeData->state_user ?? '') }}"
                                                        data-balances='@json($balanceData)'
                                                        data-total-balance="{{ $request->balance_in_usd ?? 0 }}"
                                                        title="نسخ جميع الارصدة">
                                                        <i class="fas fa-copy"></i> نسخ التقرير
                                                    </button>
                                                </div>
                                            </td>

                                            <td class="py-0.5 px-3 border-b text-center font-bold">
                                                @php
                                                    $balance = (int) $request->balance_in_usd;
                                                    $color = $balance < 0 ? 'text-red-1' : 'text-Lime';
                                                    $text = $balance > 0 ? '(دائن لكم)' : '(دائن عليكم)';
                                                @endphp
                                                <div class="flex items-center justify-center gap-2">
                                                    <span class="{{ $color }}">
                                                        {{ number_format($balance, 0, '', '') }}
                                                        {{ $text }}
                                                    </span>
                                                    <button type="button"
                                                        class="p-1 text-gray-600 transition-colors duration-200 rounded-md hover:bg-gray-200 copy-balance"
                                                        data-balance="{{ number_format($balance, 0, '', '') }}"
                                                        data-text="{{ $text }}" title="نسخ الرصيد">
                                                        <i class="fas fa-clipboard text-xs"></i>
                                                    </button>
                                                </div>
                                            </td>

                                            @foreach ($columns as $column)
                                                @php
                                                    $currencyCode = str_replace(
                                                        ['_1', '_2'],
                                                        '',
                                                        $column['receiver_column'],
                                                    );
                                                    $columnKey =
                                                        $request->receiver_id === Auth::id()
                                                            ? $column['receiver_column']
                                                            : $column['sender_column'];
                                                    $balance = $request->{$columnKey} ?? 0;
                                                    $textColor =
                                                        $balance < 0
                                                            ? 'text-red-1'
                                                            : ($balance > 0
                                                                ? 'text-Lime'
                                                                : 'text-gray-500');
                                                    $today = now()->format('Y-m-d');
                                                @endphp
                                                <td class="py-2 px-2 border-b text-center {{ $textColor }}">
                                                    <div class="flex items-center justify-center gap-1">
                                                        <a href="{{ route('transfers.Transfer2Report', [
                                                            'currency' => $currencyCode,
                                                            'clientId' => encrypt($clientId),
                                                            'from_date' => $today,
                                                            'to_date' => $today,
                                                        ]) }}"
                                                            class="underline hover:text-blue-800 transition-colors duration-300 no-underline">
                                                            {{ $balance != 0 ? number_format($balance, 0, '', '') : 'غير متوفر' }}
                                                        </a>
                                                        @if ($balance != 0)
                                                            <button type="button"
                                                                class="p-1 text-gray-500 hover:text-gray-700 copy-currency"
                                                                data-currency="{{ $currencyNames->get($currencyCode) ?? $currencyCode }}"
                                                                data-value="{{ number_format(abs($balance), 0, '', '') }}"
                                                                title="نسخ قيمة العملة">
                                                                <i class="fas fa-copy text-xs"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 5 + count($columns) }}"
                                                class="px-2 py-2 text-center text-gray-500">
                                                لا توجد طلبات صداقة حالياً.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // نسخ الرصيد الإجمالي
                        const copyBalanceButtons = document.querySelectorAll('.copy-balance');
                        copyBalanceButtons.forEach(button => {
                            button.addEventListener('click', function() {
                                const balance = this.dataset.balance;
                                const text = this.dataset.text;
                                const formattedText = `الرصيد الإجمالي: ${balance} دولار ${text}`;

                                copyToClipboard(this, formattedText);
                            });
                        });

                        // نسخ عملة محددة
                        const copyCurrencyButtons = document.querySelectorAll('.copy-currency');
                        copyCurrencyButtons.forEach(button => {
                            button.addEventListener('click', function() {
                                const currency = this.dataset.currency;
                                const value = this.dataset.value;
                                const formattedText = `${currency}: ${value}`;

                                copyToClipboard(this, formattedText);
                            });
                        });

                        // نسخ التقرير الكامل
                        const copyAllButtons = document.querySelectorAll('.copy-all-balances');
                        copyAllButtons.forEach(button => {
                            button.addEventListener('click', function() {
                                const office = this.dataset.office;
                                const location = this.dataset.location;
                                const balances = JSON.parse(this.dataset.balances);
                                const totalBalance = parseFloat(this.dataset.totalBalance);

                                let formattedText = `╔═══════════════════════════╗\n`;
                                formattedText += `   اسم المكتب: ${office}\n`;
                                formattedText += `   الموقع: ${location}\n`;
                                formattedText += `╚═══════════════════════════╝\n\n`;

                                Object.entries(balances).forEach(([code, data]) => {
                                    if (data.value != 0) {
                                        formattedText +=
                                            `• ${data.text}: ${Math.abs(data.value).toLocaleString()} ${code}\n`;
                                    }
                                });

                                formattedText += `\n═══════════════════════════\n`;
                                formattedText +=
                                    `الرصيد الإجمالي: ${Math.abs(totalBalance).toLocaleString()} دولار\n`;
                                formattedText += `${totalBalance > 0 ? '(دائن لكم)' : '(دائن عليكم)'}\n\n`;
                                formattedText += `📅 ${new Date().toLocaleDateString('ar-EG')}\n`;
                                formattedText += `⏰ ${new Date().toLocaleTimeString('ar-EG')}`;

                                copyToClipboard(this, formattedText);
                            });
                        });

                        function copyToClipboard(element, text) {
                            navigator.clipboard.writeText(text).then(() => {
                                const icon = element.querySelector('i');
                                const originalClass = icon.className;

                                icon.className = 'fas fa-check text-green-600';

                                setTimeout(() => {
                                    icon.className = originalClass;
                                }, 2000);
                            }).catch(err => {
                                console.error('Failed to copy:', err);
                            });
                        }
                    });
                </script>
                <!-- جدول الطلبات الواردة -->
                <div class="tab-pane fade" id="pills-received-request" role="tabpanel"
                    aria-labelledby="pills-received-request-tab">
                    <div class="mb-4 card">
                        <div class="text-center text-gray-100 bg-blue-500 card-header">
                            <strong>الاذونات</strong>
                        </div>
                        <div class="container mx-auto mt-3">
                            <table
                                class="w-full overflow-hidden border border-gray-300 rounded-lg shadow-md table-auto tebl"
                                style="direction: rtl;">
                                <thead class="text-center text-gray-700 bg-gray-200">
                                    <tr class="text-center">
                                        <th class="px-3 py-2 text-center border-b">#</th>
                                        <th class="px-3 py-2 text-center border-b w-32">اسم المكتب</th>
                                        <th class="px-3 py-2 text-center border-b">محدودية الرصيد</th>
                                        <th class="px-3 py-2 text-center border-b"> كلمه السر الحركات</th>
                                        <th class="px-3 py-2 text-center border-b">الوارد</th>
                                        <th class="px-3 py-2 text-center border-b">إعتماد</th>
                                        <th class="px-3 py-2 text-center border-b">إيقاف التبادل</th>
                                        <th class="px-3 py-2 text-center border-b">إخفاء الحساب</th>
                                        <th class="px-3 py-2 text-center border-b">إيقاف الرابط</th>
                                        <th class="px-3 py-2 text-center border-b">إيقاف سوري</th>

                                        <th class="px-3 py-2 text-center border-b">إدارة الأجور</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($friendRequests as $index => $request)
                                        <tr class="text-sm text-center text-gray-900 hover:bg-gray-100">
                                            <td class="py-0.5 px-3 border-b text-center bg-blue-300">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="py-0.5 px-3 border-b text-center font-bold">
                                                {{ $destinations->firstWhere('id', $request->receiver_id === Auth::id() ? $request->sender_id : $request->receiver_id)->Office_name ?? 'غير متوفر' }}
                                                <br>
                                                {{ $destinations->firstWhere(
                                                    'id',
                                                    $request->receiver_id === Auth::id() ? $request->sender_id : $request->receiver_id,
                                                )->country_user .
                                                    ' - ' .
                                                    $destinations->firstWhere('id', $request->receiver_id === Auth::id() ? $request->sender_id : $request->receiver_id)->state_user ??
                                                    'غير متوفر' }}
                                            </td>


                                            <td class="py-0.5 px-3 border-b text-center ">
                                                @if ($request->receiver_id === Auth::id())
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <input type="text" name="limited"
                                                            class="w-full px-3 py-2 text-base border border-gray-300 rounded-md shadow-sm form-control limited-input focus:outline-none focus:ring-2 focus:ring-blue-500 number-only"
                                                            value="{{ $request->Limited_2 }}"
                                                            data-id="{{ $request->id }}" required>
                                                        <button
                                                            class="p-2 text-blue-500 bg-transparent rounded-md shadow-sm btn btn-primary btn-sm update-btn hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                            data-id="{{ $request->id }}">
                                                            <i
                                                                class="text-blue-500 transition-colors duration-300 fas fa-edit hover:text-black"></i>
                                                        </button>
                                                    </div>
                                                @elseif ($request->sender_id === Auth::id())
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <input type="text" name="limited"
                                                            class="w-full px-3 py-2 text-base border border-gray-300 rounded-md shadow-sm form-control limited-input focus:outline-none focus:ring-2 focus:ring-blue-500 number-only"
                                                            value="{{ $request->Limited_1 }}"
                                                            data-id="{{ $request->id }}" required>
                                                        <button
                                                            class="p-2 text-blue-500 bg-transparent rounded-md shadow-sm btn btn-primary btn-sm update-btn hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                            data-id="{{ $request->id }}">
                                                            <i
                                                                class="text-blue-500 transition-colors duration-300 fas fa-edit hover:text-black"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    {{ $request->limited ?? 'غير متوفر' }}
                                                @endif
                                            </td>
                                            <td class="py-0.5 px-3 border-b text-center">
                                                @if ($request->receiver_id === Auth::id())
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <input type="text" name="password"
                                                            class="w-full px-3 py-2 text-base border border-gray-300 rounded-md shadow-sm form-control password-input focus:outline-none focus:ring-2 focus:ring-blue-500 number-only"
                                                            value="{{ $request->password_usd_2 }}"
                                                            data-id="{{ $request->id }}" required>
                                                        <button
                                                            class="p-2 text-blue-500 bg-transparent rounded-md shadow-sm btn btn-primary btn-sm update-password-btn hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                            data-id="{{ $request->id }}">
                                                            <i
                                                                class="text-blue-500 transition-colors duration-300 fas fa-key hover:text-black"></i>
                                                        </button>
                                                    </div>
                                                @elseif ($request->sender_id === Auth::id())
                                                    <div class="flex items-center justify-center w-full space-x-2 ">
                                                        <input type="text" name="password"
                                                            class="w-full px-3 py-2 text-base border border-gray-300 rounded-md shadow-sm form-control password-input focus:outline-none focus:ring-2 focus:ring-blue-500 number-only"
                                                            value="{{ $request->password_usd_1 }}"
                                                            data-id="{{ $request->id }}" required>
                                                        <button
                                                            class="p-2 text-blue-500 bg-transparent rounded-md shadow-sm btn btn-primary btn-sm update-password-btn hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                            data-id="{{ $request->id }}">
                                                            <i
                                                                class="text-blue-500 transition-colors duration-300 fas fa-key hover:text-black"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    كلمة المرور غير متاحة
                                                @endif
                                            </td>

                                            <td class="py-0.5 px-3 border-b text-center">
                                                @if ($request->sender_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل Stop_movements_1 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->Stop_movements_1 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->Stop_movements_1 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}"
                                                            data-field="Stop_movements_1">
                                                            {{ $request->Stop_movements_1 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @elseif ($request->receiver_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل Stop_movements_2 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->Stop_movements_2 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->Stop_movements_2 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}"
                                                            data-field="Stop_movements_2">
                                                            {{ $request->Stop_movements_2 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @else
                                                    {{ 'غير متوفر' }}
                                                @endif
                                            </td>
                                            <td class="py-0.5 px-3 border-b text-center">
                                                @if ($request->sender_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل stop_approval_1 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->stop_approval_1 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->stop_approval_1 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}"
                                                            data-field="stop_approval_1">
                                                            {{ $request->stop_approval_1 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @elseif ($request->receiver_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل stop_approval_2 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->stop_approval_2 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->stop_approval_2 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}"
                                                            data-field="stop_approval_2">
                                                            {{ $request->stop_approval_2 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @else
                                                    {{ 'غير متوفر' }}
                                                @endif
                                            </td>
                                            <td class="py-0.5 px-3 border-b text-center">
                                                @if ($request->sender_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل stop_exchange_1 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->stop_exchange_1 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->stop_exchange_1 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}"
                                                            data-field="stop_exchange_1">
                                                            {{ $request->stop_exchange_1 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @elseif ($request->receiver_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل stop_exchange_2 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->stop_exchange_2 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->stop_exchange_2 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}"
                                                            data-field="stop_exchange_2">
                                                            {{ $request->stop_exchange_2 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @else
                                                    {{ 'غير متوفر' }}
                                                @endif
                                            </td>
                                            <td class="py-0.5 px-3 border-b text-center">
                                                @if ($request->sender_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل hide_account_1 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->hide_account_1 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->hide_account_1 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}"
                                                            data-field="hide_account_1">
                                                            {{ $request->hide_account_1 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @elseif ($request->receiver_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل hide_account_2 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->hide_account_2 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->hide_account_2 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}"
                                                            data-field="hide_account_2">
                                                            {{ $request->hide_account_2 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @else
                                                    {{ 'غير متوفر' }}
                                                @endif
                                            </td>
                                            <td class="py-0.5 px-3 border-b text-center">
                                                @if ($request->sender_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل stop_link_1 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->stop_link_1 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->stop_link_1 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}" data-field="stop_link_1">
                                                            {{ $request->stop_link_1 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @elseif ($request->receiver_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل stop_link_2 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->stop_link_2 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->stop_link_2 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}" data-field="stop_link_2">
                                                            {{ $request->stop_link_2 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @else
                                                    {{ 'غير متوفر' }}
                                                @endif
                                            </td>


                                            <td class="py-0.5 px-3 border-b text-center">
                                                @if ($request->sender_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل stop_syp_1 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->stop_syp_1 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->stop_syp_1 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}" data-field="stop_syp_1">
                                                            {{ $request->stop_syp_1 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @elseif ($request->receiver_id === Auth::id())
                                                    <!-- تفعيل/إلغاء تفعيل stop_syp_2 -->
                                                    <div class="flex items-center justify-center w-full space-x-2">
                                                        <button
                                                            class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->stop_syp_2 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->stop_syp_2 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                            data-id="{{ $request->id }}" data-field="stop_syp_2">
                                                            {{ $request->stop_syp_2 ? 'إلغاء ' : 'تفعيل' }}
                                                        </button>
                                                    </div>
                                                @else
                                                    {{ 'غير متوفر' }}
                                                @endif
                                            </td>
                                            <!--    <td class="py-0.5 px-3 border-b text-center">
                                            @if ($request->sender_id === Auth::id())
<div class="flex items-center justify-center w-full space-x-2">
                                                    <button
                                                        class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->Slice_type_1 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->Slice_type_1 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                        data-id="{{ $request->id }}" data-field2="Slice_type_1">
                                                        {{ $request->Slice_type_1 ? 'الشريحة الثانية  ' : 'الشريحة الاولى' }}
                                                    </button>
                                                </div>
@elseif ($request->receiver_id === Auth::id())
<div class="flex items-center justify-center w-full space-x-2">
                                                    <button
                                                        class="toggle-stop-btn p-1 text-white font-semibold rounded-lg shadow-lg
                                                        {{ $request->Slice_type_2 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                                                        transition-transform duration-200 focus:outline-none focus:ring-2
                                                        {{ $request->Slice_type_2 ? 'focus:ring-green-600' : 'focus:ring-red-600' }}"
                                                        data-id="{{ $request->id }}" data-field2="Slice_type_2">
                                                        {{ $request->Slice_type_2 ? 'الشريحة الثانية  ' : 'الشريحة الاولى' }}
                                                    </button>
                                                </div>
@else
{{ 'غير متوفر' }}
@endif
                                        </td>-->
                                            <td class="text-center bg-gray-100 border-b">
                                                <a href="{{ route('destination.wages', ['id' => encrypt($request->receiver_id)]) }}"
                                                    class="w-full btn">
                                                    <i
                                                        class="text-blue-500 transition-colors duration-300 fas fa-edit hover:text-black"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="py-0.5 px-2 text-center text-gray-500">لا توجد
                                                طلبات
                                                صداقة حالياً.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
