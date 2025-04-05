<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800">
            حوالات التسليم الناجحة
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم العملية</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المستلم</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المبلغ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاريخ التسليم</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($transfers as $transfer)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $transfer->transaction_number }}</td>
                                <td class="px-6 py-4 text-sm">{{ $transfer->recipient->name }}</td>
                                <td class="px-6 py-4 text-sm">{{ number_format($transfer->amount, 2) }} {{ $transfer->currency->code }}</td>
                                <td class="px-6 py-4 text-sm">{{ $transfer->delivered_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <button
                                        class="view-delivery-details bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition"
                                        data-id="{{ $transfer->id }}"
                                    >
                                        تفاصيل التسليم
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">لا توجد حوالات مسلمة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $transfers->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Details Modal -->
    <div id="deliveryDetailsModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-4 border-b pb-2">تفاصيل عملية التسليم</h3>
                    <div id="deliveryContent" class="space-y-4"></div>
                    <div class="mt-6 flex justify-end">
                        <button
                            onclick="closeDeliveryModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg"
                        >
                            إغلاق
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('.view-delivery-details').forEach(btn => {
            btn.addEventListener('click', function() {
                const transferId = this.dataset.id;
                fetch(`{{ route('delivered.show', '') }}/${transferId}`)
                    .then(response => response.json())
                    .then(data => {
                        const transfer = data.transfer;
                        const content = `
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="font-medium">رقم العملية:</p>
                                    <p>${transfer.transaction_number}</p>
                                </div>
                                <div>
                                    <p class="font-medium">تاريخ التسليم:</p>
                                    <p>${new Date(transfer.delivered_at).toLocaleString()}</p>
                                </div>
                                <div>
                                    <p class="font-medium">المستلم:</p>
                                    <p>${transfer.recipient.name}</p>
                                </div>
                                <div>
                                    <p class="font-medium">المبلغ:</p>
                                    <p>${transfer.amount} ${transfer.currency.code}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="font-medium">إثباتات التسليم:</p>
                                    <div class="grid grid-cols-3 gap-2 mt-2">
                                        ${data.proofs.map(proof => `
                                            <img
                                                src="/storage/${proof.file_path}"
                                                class="w-full h-32 object-cover border rounded-lg cursor-pointer hover:opacity-75"
                                                onclick="window.open('/storage/${proof.file_path}', '_blank')"
                                            >
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        `;
                        document.getElementById('deliveryContent').innerHTML = content;
                        document.getElementById('deliveryDetailsModal').classList.remove('hidden');
                    });
            });
        });

        function closeDeliveryModal() {
            document.getElementById('deliveryDetailsModal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
