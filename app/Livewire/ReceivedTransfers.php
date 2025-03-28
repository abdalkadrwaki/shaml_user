<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReceivedTransfers extends Component
{
    public $search = '';
    public $selectedTransferId;
    public $selectedTransfer;

    // الحقول الخاصة بنموذج تسليم الحوالة
    public $password = '';
    public $recipientInfo = '';
    public $imageData = ''; // يُفترض أن تكون سلسلة base64
    public $deliveryError = '';

    protected $rules = [
        'password'      => 'required|string',
        'recipientInfo' => 'required|string|max:255',
        'imageData'     => ['required', 'string', 'regex:/^data:image\/(\w+);base64,/']
    ];

    public function mount()
    {
        if (!Auth::check()) {
            abort(403, 'يجب تسجيل الدخول.');
        }
        if (Gate::denies('manage-Lessons')) {
            abort(403, 'ليس لديك الصلاحية.');
        }
    }

    public function render()
    {
        $query = Transfer::with(['currency', 'sender'])
            ->where('destination', Auth::id())
            ->where('transaction_type', 'Transfer')
            ->whereIn('status', ['Pending', 'Frozen']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('movement_number', 'like', '%' . $this->search . '%')
                  ->orWhere('recipient_name', 'like', '%' . $this->search . '%');
            });
        }

        $receivedTransfers = $query->orderBy('created_at', 'desc')->get();

        return view('livewire.received-transfers', compact('receivedTransfers'));
    }

    // لفتح المودال مع تحميل بيانات الحوالة المُختارة
    public function selectTransfer($transferId)
    {
        $this->selectedTransferId = $transferId;
        $this->selectedTransfer = Transfer::with(['currency', 'sender'])->find($transferId);
        // إعادة تعيين الحقول الخاصة بالمودال
        $this->password      = '';
        $this->recipientInfo = '';
        $this->imageData     = '';
        $this->deliveryError = '';
        $this->emit('openDeliverModal');
    }

    // تبديل حالة التجميد
    public function toggleFreeze($transferId)
    {
        $transfer = Transfer::find($transferId);
        if (!$transfer) {
            session()->flash('error', 'الحوالة غير موجودة.');
            return;
        }
        if ($transfer->destination !== Auth::id()) {
            session()->flash('error', 'غير مصرح لك بتعديل هذه الحوالة.');
            return;
        }
        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            session()->flash('error', 'لا يمكن تعديل حالة هذه الحوالة.');
            return;
        }
        $transfer->status = ($transfer->status === 'Frozen') ? 'Pending' : 'Frozen';
        $transfer->statuss = $transfer->status;
        $transfer->save();
        session()->flash('success', 'تم تغيير حالة الحوالة.');
    }

    // التحقق من كلمة المرور (يمكن استدعاؤها قبل عملية التسليم)
    public function verifyPassword()
    {
        $transfer = Transfer::find($this->selectedTransferId);
        if (!$transfer) {
            $this->deliveryError = 'الحوالة غير موجودة.';
            return;
        }
        if ($transfer->destination !== Auth::id()) {
            $this->deliveryError = 'غير مصرح لك بهذه الحوالة.';
            return;
        }
        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            $this->deliveryError = 'لا يمكن التحقق من كلمة المرور للحوالة.';
            return;
        }
        if ($transfer->password !== $this->password) {
            $this->deliveryError = 'كلمة المرور غير صحيحة.';
            return;
        }
        $this->deliveryError = '';
        session()->flash('success', 'تم التحقق من كلمة المرور بنجاح.');
    }

    // تسليم الحوالة بعد التحقق
    public function deliverTransfer()
    {
        $transfer = Transfer::find($this->selectedTransferId);
        if (!$transfer) {
            $this->deliveryError = 'الحوالة غير موجودة.';
            return;
        }
        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            $this->deliveryError = 'لا يمكن تسليم الحوالة لأنها تم تسليمها أو إلغاؤها مسبقًا.';
            return;
        }
        if ($transfer->destination !== Auth::id()) {
            $this->deliveryError = 'غير مصرح لك بتعديل هذه الحوالة.';
            return;
        }

        $validated = $this->validate();

        DB::beginTransaction();
        try {
            // إعادة تحميل الحوالة بقفل لتفادي التعديل المتزامن
            $transfer = Transfer::where('id', $transfer->id)
                ->lockForUpdate()
                ->first();

            if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
                $this->deliveryError = 'لا يمكن تسليم الحوالة لأنها تم تسليمها أو إلغاؤها مسبقًا.';
                DB::rollBack();
                return;
            }

            // معالجة الصورة (التأكد من كونها base64 واستخراج الامتداد)
            if (preg_match('/^data:image\/(\w+);base64,/', $validated['imageData'], $type)) {
                $imageData = substr($validated['imageData'], strpos($validated['imageData'], ',') + 1);
                $extension = strtolower($type[1]);
                $imageDecoded = base64_decode($imageData);
                if ($imageDecoded === false) {
                    $this->deliveryError = 'فشل في فك تشفير الصورة.';
                    DB::rollBack();
                    return;
                }
                if (strlen($imageDecoded) > (2 * 1024 * 1024)) {
                    $this->deliveryError = 'حجم الصورة أكبر من الحجم المسموح به.';
                    DB::rollBack();
                    return;
                }
            } else {
                $this->deliveryError = 'تنسيق الصورة غير صالح.';
                DB::rollBack();
                return;
            }

            // حفظ الصورة باستخدام رقم الحركة كاسم للملف
            $fileName = $transfer->movement_number . '.' . $extension;
            $filePath = 'recipient_image/' . $fileName;
            Storage::disk('public')->put($filePath, $imageDecoded);

            // تحديث بيانات الحوالة وتغيير الحالة إلى Delivered
            $transfer->recipient_info = strip_tags($validated['recipientInfo']);
            $transfer->status = 'Delivered';
            $transfer->statuss = 'Delivered';
            $transfer->save();

            DB::commit();
            session()->flash('success', 'تم تسليم الحوالة بنجاح.');

            // إعادة تعيين البيانات وإغلاق المودال
            $this->selectedTransfer = null;
            $this->selectedTransferId = null;
            $this->password = '';
            $this->recipientInfo = '';
            $this->imageData = '';
            $this->emit('closeDeliverModal');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("فشل تسليم الحوالة: " . $e->getMessage());
            $this->deliveryError = 'حدث خطأ أثناء تسليم الحوالة.';
        }
    }
}
