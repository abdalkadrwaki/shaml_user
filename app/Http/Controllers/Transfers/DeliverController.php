<?php

namespace App\Http\Controllers\Transfers;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliverController extends Controller
{
    /**
     * عرض صفحة الحوالات المرسلة مع الترقيم.
     *
     * يتم جلب الحوالات التي تنتمي للمستخدم الحالي ومن نوع "Transfer" فقط.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // استخدام الترقيم لتحسين الأداء
        $transfers = Transfer::with(['currency', 'recipient', 'receivedCurrency'])
            ->where('user_id', Auth::id())
            ->where('transaction_type', 'Transfer')
            ->where('status', '!=', 'Delivered')
            ->orderBy('created_at', 'desc')
            ->paginate(13); // تقليل الحجم باستخدام الترقيم

        return view('transfers.deliver', compact('transfers'));
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

        if ($transfer->user_id !== Auth::id()) {
            return response()->json(['error' => 'غير مصرح لك'], 403);
        }

        if ($transfer->status !== 'Delivered') {
            return response()->json(['error' => 'هذه الحوالة ليست في حالة مسلمة'], 400);
        }

        $imagePath = asset('storage/recipient_image/' . $transfer->movement_number . '.png');

        return response()->json([
            'transfer' => $transfer,
            'image' => $imagePath,
        ]);
    }
}
