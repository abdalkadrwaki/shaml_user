<div class=" mt-4">
    <div class="card-body">
    <!-- عرض الرسائل (نجاح أو فشل) -->
    @if (session()->has('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @elseif(session()->has('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    <!-- أزرار التنقل (Tabs) -->
<ul class="p-1 mb-3 nav nav-pills justify-content-center gap-2" id="pills-tab" role="tablist" style="width: 100%; display: flex;">
    <li class="nav-item flex-fill" role="presentation">
        <a class="px-2 py-2 text-center bg-blue-900  tex nav-link active"
           id="pills-send-request-tab" data-bs-toggle="pill" href="#pills-send-request" role="tab"
           aria-controls="pills-send-request" aria-selected="true">
            ارسال طلب ربط
        </a>
    </li>
    <li class="nav-item flex-fill" role="presentation">
        <a class="px-2 py-2 text-center bg-blue-900 text-white nav-link"
           id="pills-received-request-tab" data-bs-toggle="pill" href="#pills-received-request" role="tab"
           aria-controls="pills-received-request" aria-selected="false">
            طلبات الصداقة الواردة
            <span class="badge bg-danger ms-1"></span>
        </a>
    </li>
    <li class="nav-item flex-fill" role="presentation">
        <a class="px-2 py-2 text-center bg-blue-900 text-white nav-link"
           id="pills-sent-request-tab" data-bs-toggle="pill" href="#pills-sent-request" role="tab"
           aria-controls="pills-sent-request" aria-selected="false">
            طلباتي المرسلة
            <span class="badge bg-danger ms-1"></span>
        </a>
    </li>
    <li class="nav-item flex-fill" role="presentation">
        <a class="px-2 py-2 text-center bg-blue-900 text-white nav-link"
           id="pills-accepted-request-tab" data-bs-toggle="pill" href="#pills-accepted-request" role="tab"
           aria-controls="pills-accepted-request" aria-selected="false">
           طلبات الصداقة المقبولة
           <span class="badge bg-danger ms-1"></span>
        </a>
    </li>
</ul>
<Style>

</Style>
<div >
    <!-- محتوى التبويبات -->
    <div class="tab-content" id="pills-tabContent">

        <div class="tab-pane fade show active" id="pills-send-request" role="tabpanel"
            aria-labelledby="pills-send-request-tab">
            <div class="card mb-4">
                <div class="card-header text-center bg-blue-500 text-white">
                    <strong>إرسال طلب صداقة</strong>
                </div>
                <div class="table-responsive p-6 m-4 text-center">
                    <input type="text" wire:model="link_number" wire:keyup="fetchUserName"
                        class="form-control number-only  rounded-md  border-gray-300r" placeholder="أدخل رقم لينك المستخدم">
                    @if ($friendRequestStatus)
                        <div class="mt-3">
                            <strong class="badge bg-success text-white">{{ $friendRequestStatus }}</strong>
                        </div>
                    @endif
                    <button wire:click="sendRequest" class="btn btn-primary mt-3"
                        @if (!$userName) disabled @endif>إرسال طلب صداقة</button>
                </div>
            </div>
        </div>

        <!-- جدول الطلبات الواردة -->
        <div class="tab-pane fade" id="pills-received-request" role="tabpanel"
            aria-labelledby="pills-received-request-tab">
            <div class="card mb-4">
                <div class="card-header text-center bg-blue-500 text-white">
                    <strong>طلبات الصداقة الواردة</strong>
                </div>
                <div class="container mx-auto mt-3">
                    <table
                    class="w-full overflow-hidden border border-gray-300 rounded-lg shadow-md table-auto tebl"
                    style="direction: rtl;">
                    <thead class="text-center text-gray-700 bg-gray-200">
                        <tr class="text-center">
                                <th class="px-3 py-2 text-center border-b">اسم المرسل</th>
                                <th class="px-3 py-2 text-center border-b">العمل</th>
                                <th class="px-3 py-2 text-center border-b">العمل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($friendRequests as $request)
                                @if ($request->status == 'pending' && $request->receiver_id == Auth::id())
                                <tr class="text-sm text-center text-gray-500 hover:bg-gray-100">
                                        <td  class="px-2 py-2 text-center border-b">{{ $request->sender->Office_name }}</td>
                                        <td  class="px-2 py-2 text-center border-b">{{ $request->created_at }}</td>
                                        <td  class="px-2 py-2 text-center border-b">
                                            <button wire:click="acceptRequest({{ $request->id }})"
                                                class="btn btn-success btn-sm">قبول</button>
                                            <button wire:click="rejectRequest({{ $request->id }})"
                                                class="btn btn-danger btn-sm">رفض</button>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">لا توجد طلبات صداقة واردة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- جدول الطلبات المرسلة -->
        <div class="tab-pane fade" id="pills-sent-request" role="tabpanel" aria-labelledby="pills-sent-request-tab">
            <div class="card mb-4">
                <div class="card-header text-center bg-blue-500 text-white">
                    <strong>طلباتي المرسلة</strong>
                </div>
                <div class="container mx-auto mt-3">
                    <table
                    class="w-full overflow-hidden border border-gray-300 rounded-lg shadow-md table-auto tebl"
                    style="direction: rtl;">
                    <thead class="text-center text-gray-700 bg-gray-200">
                        <tr class="text-center">
                                <th class="px-3 py-2 text-center border-b">إلى</th>
                                <th class="px-3 py-2 text-center border-b">العمل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($friendRequests as $request)
                                @if ($request->status == 'pending' && $request->sender_id == Auth::id())
                                <tr class="text-sm text-center text-gray-500 hover:bg-gray-100">
                                        <td  class="px-2 py-2 text-center border-b">{{ $request->receiver->name }}</td>
                                        <td  class="px-2 py-2 text-center border-b">
                                            <button wire:click="cancelRequest({{ $request->id }})"
                                                class="btn btn-warning btn-sm">إلغاء الطلب</button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- جدول الطلبات المقبولة -->
        <div class="tab-pane fade" id="pills-accepted-request" role="tabpanel"
            aria-labelledby="pills-accepted-request-tab">
            <div class="card mb-4">
                <div class="card-header text-center bg-blue-500 text-white">
                    <strong>طلبات الصداقة المقبولة</strong>
                </div>
                <div class="container mx-auto mt-3">
                    <table
                    class="w-full overflow-hidden border border-gray-300 rounded-lg shadow-md table-auto tebl"
                    style="direction: rtl;">
                    <thead class="text-center text-gray-700 bg-gray-200">
                        <tr class="text-center">
                                <th class="px-3 py-2 text-center border-b">اسم مكتب</th>
                                <th class="px-3 py-2 text-center border-b">عنوان</th>
                                <th class="px-3 py-2 text-center border-b">حالة</th>
                                <th class="px-3 py-2 text-center border-b">تاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($friendRequests as $request)
                                @if ($request->status == 'accepted')
                                <tr class="text-sm text-center text-gray-500 hover:bg-gray-100">
                                        <td class="px-2 py-2 text-center border-b">
                                            {{ $request->sender_id == Auth::id() ? $request->receiver->Office_name : $request->sender->Office_name }}
                                        </td>
                                        <td class="px-2 py-2 text-center border-b">
                                            {{ $request->sender_id == Auth::id() ? $request->receiver->country_user : $request->sender->country_user }}
                                        </td>
                                        <td class="px-2 py-2 text-center border-b">{{ $request->status }}</td>
                                        <td class="px-2 py-2 text-center border-b">{{ $request->created_at }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
</div>

