<?php

namespace App\Http\Controllers\Transfers;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\FriendRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Gate;
use App\Services\GenerateTransferImageService;
use Illuminate\Support\Facades\Cache;

class SentTransfersApprovalController extends Controller
{
    /**
     * عرض صفحة الحوالات المرسلة مع الترقيم.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (Gate::denies('manage-Lessons')) {
            abort(403, 'ليس لديك الصلاحية للوصول إلى هذه الصفحة.');
        }

        // جلب الحوالات باستخدام الترقيم
        $transfers = Transfer::with(['currency', 'recipient'])
            ->where('user_id', Auth::id())
            ->where('transaction_type', 'Credit')
            ->orderBy('created_at', 'desc')
            ->paginate(100); // استخدام الترقيم لتحسين الأداء

        return view('transfers.sentapproval', compact('transfers'));
    }

    /**
     * جلب بيانات الحوالات المرسلة عبر AJAX.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransfersData()
    {
        if (Gate::denies('manage-Lessons')) {
            abort(403, 'ليس لديك الصلاحية للوصول إلى هذه الصفحة.');
        }

        // استرجاع البيانات باستخدام التحديد الجزئي لتقليل الحجم
        $transfers = Transfer::select('id', 'recipient_name', 'sent_currency', 'sent_amount', 'note', 'created_at', 'status')
            ->where('user_id', Auth::id())
            ->where('transaction_type', 'Transfer')
            ->orderBy('created_at', 'desc')
            ->paginate(13);

        // تحميل العلاقة مع العملة لتجنب الاستعلامات المتعددة
        $transfers->load('currency');

        // معالجة البيانات وتحويلها لتقليل الحجم
        $transfers->getCollection()->transform(function ($transfer) {
            $currencyName = $transfer->currency ? $transfer->currency->name_ar : $transfer->sent_currency;
            $transfer->sent_currency = $currencyName;

            $actions = '';
            if ($transfer->status === 'Pending') {
                $actions .= '
                    <form action="' . route('transfers.sent.destroy', $transfer->id) . '" method="POST" style="display:inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger" onclick="return confirm(\'هل أنت متأكد من إلغاء الحوالة؟\')">إلغاء</button>
                    </form>
                ';
            }
            $actions .= '
                <a href="' . route('transfers.sent.print', $transfer->id) . '" class="btn btn-secondary">طباعة</a>
            ';
            $transfer->actions = $actions;

            return $transfer;
        });

        return response()->json(['data' => $transfers]);
    }

    /**
     * جلب تفاصيل الحوالة وصورة المستلم للحوالات المسلمة.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransferDetails($id)
    {
        $transfer = Transfer::findOrFail($id);

        // التأكد من أن الحوالة تابعة للمستخدم الحالي
        if ($transfer->user_id !== Auth::id()) {
            return response()->json(['error' => 'غير مصرح لك'], 403);
        }

        // التأكد من أن الحالة Delivered
        if ($transfer->status !== 'Delivered') {
            return response()->json(['error' => 'هذه الحوالة ليست في حالة مسلمة'], 400);
        }

        // بناء مسار صورة المستلم (يفترض أن الصور مخزنة في storage/app/public/recipient_image)
        $imagePath = asset('storage/recipient_image/' . $transfer->movement_number . '.png');

        return response()->json([
            'transfer' => $transfer,
            'image'    => $imagePath,
        ]);
    }

    /**
     * إلغاء الحوالة (تحديث الحالة إلى "Cancelled" بدلاً من حذفها).
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $transfer = Transfer::findOrFail($id);

        if (
            $transfer->user_id !== Auth::id() ||
            $transfer->status !== 'Pending' ||
            !in_array($transfer->transaction_type, ['Transfer', 'Credit'])
        ) {
            $errorBag = new MessageBag(['error' => 'لا يمكنك إلغاء حوالة ليست في حالة انتظار أو من النوع الصحيح.']);
            return redirect()->route('transfers.sentapproval')->withErrors($errorBag);
        }

        DB::beginTransaction();

        try {
            // تحديث حالة الحوالة إلى "Cancelled" فقط
            $transfer->update(['status' => 'Cancelled']);

            DB::commit();

            return redirect()->route('transfers.sentapproval')
                ->with('success', 'تم إلغاء الحوالة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء إلغاء الحوالة:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorBag = new MessageBag(['error' => 'حدث خطأ أثناء إلغاء الحوالة.']);
            return redirect()->route('transfers.sentapproval')->withErrors($errorBag);
        }
    }

    /**
     * إنشاء وعرض صورة الحوالة.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function printImage($id)
    {
        $transfer = Transfer::findOrFail($id);

        if ($transfer->user_id !== Auth::id() || !in_array($transfer->transaction_type, ['Transfer', 'Credit'])) {
            return response()->json(['error' => 'لا يمكنك طباعة هذه الحوالة.'], 403);
        }

        try {
            $generateService = new GenerateTransferImageService();
            $base64Image = $generateService->generateTransferImage($id);

            return response()->json(['base64Image' => $base64Image]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء توليد الصورة.'], 500);
        }
    }
}
