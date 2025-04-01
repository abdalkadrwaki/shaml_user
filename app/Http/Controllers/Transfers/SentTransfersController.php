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
use App\Services\BalanceService;

class SentTransfersController extends Controller
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
            ->where('status', '!=', 'Archived')
            ->orderBy('created_at', 'desc')
            ->paginate(13); // تقليل الحجم باستخدام الترقيم

        return view('transfers.sent.index', compact('transfers'));
    }

    /**
     * جلب بيانات الحوالات المرسلة عبر AJAX مع التحميل المسبق للعلاقات.
     *
     * يتم جلب بيانات الحوالات من نوع "Transfer" فقط.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransfersData()
    {
       
        // استخدام التحديد الجزئي للبيانات لتحسين الأداء
        $transfers = Transfer::with('currency')
            ->where('user_id', Auth::id())
            ->where('transaction_type', 'Transfer')
            ->orderBy('created_at', 'desc')
            ->select([
                'id',
                'recipient_name',
                'recipient_mobile',
                'sent_currency',
                'received_currency',
                'sent_amount',
                'received_amount',
                'fees',
                'note',
                'created_at',
                'status'
            ])
            ->get()
            ->map(function ($transfer) {
                $currencyName = $transfer->currency ? $transfer->currency->name_ar : $transfer->sent_currency;
                $transfer->actions = '';

                if ($transfer->status === 'Pending') {
                    $transfer->actions = '
                        <a href="' . route('transfers.sent.edit', $transfer->id) . '" class="btn btn-primary">تعديل</a>
                        <form action="' . route('transfers.sent.destroy', $transfer->id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger" onclick="return confirm(\'هل أنت متأكد من إلغاء الحوالة؟\')">إلغاء</button>
                        </form>
                    ';
                }

                $transfer->actions .= '
                    <a href="' . route('transfers.sent.print', $transfer->id) . '" class="btn btn-secondary">طباعة</a>
                ';

                $transfer->sent_currency = $currencyName;
                return $transfer;
            });

        return response()->json(['data' => $transfers]);
    }

    /**
     * عرض نموذج تعديل الحوالة.
     *
     * التأكد من أن الحوالة تابعة للمستخدم وفي حالة "Pending" ومن نوع "Transfer" فقط.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        $transfer = Transfer::findOrFail($id);

        if (
            $transfer->user_id !== Auth::id() ||
            $transfer->status !== 'Pending' ||
            $transfer->transaction_type !== 'Transfer'
        ) {
            $errorBag = new MessageBag(['error' => 'لا يمكنك تعديل حوالة ليست في حالة انتظار أو ليست من نوع Transfer.']);
            return redirect()->route('transfers.sent.index')->withErrors($errorBag);
        }

        return view('transfers.sent.edit', compact('transfer'));
    }

    /**
     * تحديث الحوالة.
     *
     * التأكد من أن الحوالة تابعة للمستخدم وفي حالة "Pending" ومن نوع "Transfer" فقط.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $transfer = Transfer::findOrFail($id);

        if (
            $transfer->user_id !== Auth::id() ||
            $transfer->status !== 'Pending' ||
            $transfer->transaction_type !== 'Transfer'
        ) {
            $errorBag = new MessageBag(['error' => 'لا يمكنك تعديل حوالة ليست في حالة انتظار أو ليست من نوع Transfer.']);
            return redirect()->route('transfers.sent.index')->withErrors($errorBag);
        }

        $validated = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_mobile' => 'required|numeric',
        ]);

        $transfer->update($validated);

        return redirect()->route('transfers.sent.index')
            ->with('success', 'تم تحديث الحوالة بنجاح.');
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

    /**
     * إلغاء الحوالة.
     *
     * عند الإلغاء، يتم تحديث حالة الحوالة الأصلية إلى "Archived"
     * ويتم إنشاء قيد حوالة عكسي جديد بنفس بيانات الحوالة مع حالة "Cancelled".
     * كما يتم تحديث الرصيد بالدولار باستخدام BalanceService::updateBalanceInUSD مع عكس العملية.
     *
     * التأكد من أن الحوالة تابعة للمستخدم وفي حالة "Pending" ومن نوع "Transfer" فقط.
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
            $transfer->transaction_type !== 'Transfer'
        ) {
            $errorBag = new MessageBag(['error' => 'لا يمكنك إلغاء حوالة ليست في حالة انتظار أو ليست من نوع Transfer.']);
            return redirect()->route('transfers.sent.index')->withErrors($errorBag);
        }

        DB::beginTransaction();

        try {
            // استرجاع بيانات طلب الصداقة
            $friendRequest = FriendRequest::where(function ($query) use ($transfer) {
                $query->where('sender_id', $transfer->user_id)
                      ->where('receiver_id', $transfer->destination);
            })
            ->orWhere(function ($query) use ($transfer) {
                $query->where('receiver_id', $transfer->user_id)
                      ->where('sender_id', $transfer->destination);
            })
            ->where('status', 'accepted')
            ->first();

            if ($friendRequest) {
                $currencyColumn1 = strtoupper($transfer->sent_currency) . '_1';
                $currencyColumn2 = strtoupper($transfer->sent_currency) . '_2';

                if ($friendRequest->sender_id == $transfer->user_id) {
                    $friendRequest->decrement($currencyColumn1, $transfer->sent_amount + ($transfer->fees ?? 0));
                    $friendRequest->increment($currencyColumn2, $transfer->sent_amount + ($transfer->fees ?? 0));
                } elseif ($friendRequest->receiver_id == $transfer->user_id) {
                    $friendRequest->decrement($currencyColumn2, $transfer->sent_amount + ($transfer->fees ?? 0));
                    $friendRequest->increment($currencyColumn1, $transfer->sent_amount + ($transfer->fees ?? 0));
                }
            }

            // تحديث الرصيد بالدولار بعكس العملية
            $totalAmount = $transfer->sent_amount + ($transfer->fees ?? 0);
            BalanceService::updateBalanceInUSD(
                $friendRequest,
                $transfer->sent_currency,
                $totalAmount,
                !(Auth::id() === $friendRequest->sender_id),
                $transfer->destination
            );

            // تحديث حالة الحوالة الأصلية إلى "Cancelled"
            $transfer->update(['status' => 'Cancelled']);

            // إنشاء قيد حوالة عكسي جديد بنفس بيانات الحوالة مع حالة "Archived"
            $reverseTransfer = $transfer->replicate();
            $reverseTransfer->status = 'Archived';
            $reverseTransfer->save();

            DB::commit();

            return redirect()->route('transfers.sent.index')
                ->with('success', 'تم إلغاء الحوالة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ أثناء إلغاء الحوالة:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorBag = new MessageBag(['error' => 'حدث خطأ أثناء إلغاء الحوالة.']);
            return redirect()->route('transfers.sent.index')->withErrors($errorBag);
        }
    }

    /**
     * إنشاء وعرض صورة الحوالة.
     *
     * عند الضغط على زر الطباعة في الجدول، يتم تمرير معرّف الحوالة
     * ثم تستدعي هذه الدالة خدمة إنشاء الصورة وتعرض النتيجة.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function printImage($id)
    {
        $transfer = Transfer::findOrFail($id);

        if ($transfer->user_id !== Auth::id() || $transfer->transaction_type !== 'Transfer') {
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
