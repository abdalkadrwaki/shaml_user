<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FriendRequestComponent extends Component
{
    public $link_number; // رقم لينك المستخدم
    public $userName; // اسم المستخدم المسترجع
    public $friendRequestStatus = null; // حالة الطلب (للعرض)

    /**
     * يتم تنفيذ هذه الدالة فور تحميل المكون.
     * يتم التحقق من صلاحية الوصول للمستخدم.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function fetchUserName()
    {
        // التحقق من أن الرقم عبارة عن قيمة عددية (يمكن تعديل ذلك حسب الحاجة)
        $this->validate([
            'link_number' => 'required|numeric',
        ]);

        if (!$this->link_number) {
            $this->userName = null;
            $this->friendRequestStatus = null;
            return;
        }

        // البحث عن المستخدم بناءً على الرقم
        $user = User::where('link_number', $this->link_number)->first();

        if ($user) {
            if ($user->id === Auth::id()) {
                $this->friendRequestStatus = 'لا يمكنك إرسال طلب لنفسك';
                $this->userName = null;
            } else {
                $this->userName = $user->name;
                $this->friendRequestStatus = "سيتم إرسال طلب إلى: {$user->name}";
            }
        } else {
            $this->friendRequestStatus = 'المستخدم غير موجود';
            $this->userName = null;
        }
    }

    /**
     * دالة لإرسال طلب الصداقة مع تنفيذ التحقق من معدل الطلبات.
     *
     * @return void
     */
    public function sendRequest()
    {
        // التحقق من صحة المدخل
        $this->validate([
            'link_number' => 'required|numeric',
        ]);

        // حماية معدل الطلبات: منع إرسال طلبات متعددة خلال فترة قصيرة (مثلاً 10 ثوانٍ)
        $cacheKey = 'friend-request-sender:' . Auth::id();
        if (Cache::has($cacheKey)) {
            session()->flash('error', 'يرجى الانتظار قبل إرسال طلب آخر.');
            return;
        }
        // حفظ مؤقت بسيط لمدة 10 ثوانٍ
        Cache::put($cacheKey, true, 10);

        // بدء معاملة قاعدة بيانات لحماية العملية
        DB::beginTransaction();
        try {
            // البحث عن المستخدم المستقبل بناءً على link_number
            $receiver = User::where('link_number', $this->link_number)->first();
            if (!$receiver) {
                session()->flash('error', 'المستخدم غير موجود');
                DB::rollBack();
                return;
            }

            // منع إرسال طلب لنفس المستخدم
            if ($receiver->id === Auth::id()) {
                session()->flash('error', 'لا يمكنك إرسال طلب لنفسك');
                DB::rollBack();
                return;
            }

            // التحقق من وجود طلب معلق مسبقاً بين المستخدمين
            $existingRequest = FriendRequest::where(function ($query) use ($receiver) {
                $query->where('sender_id', Auth::id())
                      ->where('receiver_id', $receiver->id)
                      ->where('status', 'pending');
            })->orWhere(function ($query) use ($receiver) {
                $query->where('sender_id', $receiver->id)
                      ->where('receiver_id', Auth::id())
                      ->where('status', 'pending');
            })->first();

            if ($existingRequest) {
                session()->flash('error', 'يوجد طلب معلق بالفعل بينك وبين هذا المستخدم');
                DB::rollBack();
                return;
            }

            // إرسال طلب الصداقة
            FriendRequest::create([
                'sender_id'   => Auth::id(),
                'receiver_id' => $receiver->id,
                'status'      => 'pending',
            ]);

            // إعادة تعيين القيم
            $this->reset(['link_number', 'userName', 'friendRequestStatus']);
            session()->flash('success', 'تم إرسال طلب الصداقة');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء إرسال طلب الصداقة', [
                'user_id'       => Auth::id(),
                'link_number'   => $this->link_number,
                'error_message' => $e->getMessage(),
            ]);
            session()->flash('error', 'حدث خطأ أثناء إرسال الطلب، يرجى المحاولة لاحقاً.');
        }
    }

    /**
     * دالة لقبول طلب صداقة.
     *
     * @param int $requestId
     * @return void
     */
    public function acceptRequest($requestId)
    {
        DB::beginTransaction();
        try {
            $request = FriendRequest::find($requestId);
            if ($request && $request->receiver_id == Auth::id()) {
                $request->update(['status' => 'accepted']);
                session()->flash('success', 'تم قبول طلب الصداقة');
            } else {
                session()->flash('error', 'لا يمكنك قبول هذا الطلب');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء قبول طلب الصداقة', [
                'user_id'       => Auth::id(),
                'request_id'    => $requestId,
                'error_message' => $e->getMessage(),
            ]);
            session()->flash('error', 'حدث خطأ أثناء قبول الطلب، يرجى المحاولة لاحقاً.');
        }
    }

    /**
     * دالة لرفض طلب صداقة.
     *
     * @param int $requestId
     * @return void
     */
    public function rejectRequest($requestId)
    {
        DB::beginTransaction();
        try {
            $request = FriendRequest::find($requestId);
            if ($request && $request->receiver_id == Auth::id()) {
                $request->delete();
                session()->flash('success', 'تم رفض طلب الصداقة');
            } else {
                session()->flash('error', 'لا يمكنك رفض هذا الطلب');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء رفض طلب الصداقة', [
                'user_id'       => Auth::id(),
                'request_id'    => $requestId,
                'error_message' => $e->getMessage(),
            ]);
            session()->flash('error', 'حدث خطأ أثناء رفض الطلب، يرجى المحاولة لاحقاً.');
        }
    }

    /**
     * دالة لإلغاء طلب صداقة.
     *
     * @param int $requestId
     * @return void
     */
    public function cancelRequest($requestId)
    {
        DB::beginTransaction();
        try {
            $request = FriendRequest::find($requestId);
            if ($request && $request->sender_id == Auth::id()) {
                $request->delete();
                session()->flash('success', 'تم إلغاء طلب الصداقة');
            } else {
                session()->flash('error', 'لا يمكنك إلغاء هذا الطلب');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء إلغاء طلب الصداقة', [
                'user_id'       => Auth::id(),
                'request_id'    => $requestId,
                'error_message' => $e->getMessage(),
            ]);
            session()->flash('error', 'حدث خطأ أثناء إلغاء الطلب، يرجى المحاولة لاحقاً.');
        }
    }

    /**
     * دالة عرض الصفحة.
     *
     * @return \Illuminate\شView\View
     */
    public function render()
    {
        // الحصول على جميع الطلبات المتعلقة بالمستخدم
        $friendRequests = FriendRequest::where(function ($query) {
            $query->where('sender_id', Auth::id())
                  ->orWhere('receiver_id', Auth::id());
        })->get();

        // حساب عدد الطلبات الواردة المعلقة
        $pendingReceivedRequestsCount = FriendRequest::where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        return view('livewire.friend-request-component', compact('friendRequests', 'pendingReceivedRequestsCount'));
    }
}
