<?php

namespace App\Http\Controllers\Transformation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Currency;
use App\Models\User;
use App\Models\Transfer;
use App\Models\FriendRequest;
use App\Services\BalanceService;
use App\Services\GenerateTransferImageService;
use App\Services\FriendService;
use App\Jobs\GenerateTransferImageJob;
use App\Events\TransferCountUpdated;
use Carbon\Carbon;
use App\Events\UndefinedErrorOccurred;
// عند تحديث العداد (مثلاً بعد حفظ عملية جديدة في قاعدة البيانات)


class TransferController extends Controller
{
    /**
     * عرض صفحة إنشاء الحوالة.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {


        $currencies = Currency::activeCurrencies();
        $destinations = FriendService::loadDestinations();
        return view('dashboard', compact('currencies', 'destinations'));
    }



    /**
     * جلب عنوان الجهة بناءً على ID الجهة.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDestinationAddress(Request $request)
    {
        $destinationId = $request->input('destination_id');
        $user = User::find($destinationId);

        if ($user) {
            return response()->json(['address' => $user->user_address]);
        } else {
            return response()->json(['address' => null], 404);
        }
    }



    /**
     * تنفيذ عملية الحوالة المالية بعد التحقق من كافة الشروط.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
{


    try {
        $validated = $request->validate([
            'recipient_name'     => 'required|string|max:255',
            'recipient_mobile'   => 'required|numeric|regex:/^05\d{8}$/',
            'destination'        => 'required|exists:users,id',
            'sent_currency'      => 'required|in:USD,TRY,EUR,SAR,SYP',
            'sent_amount'        => 'required|numeric|min:0.01|max:1000000',
            'received_currency'  => 'required|in:USD,TRY,EUR,SAR,SYP',
            'received_amount'    => 'required|numeric|min:0.01|max:1000000',
            'fees'               => 'nullable|numeric|min:0|max:1000',
            'exchange_rate'      => 'nullable|numeric|min:0',
            'note'               => 'nullable|string|max:500'
        ]);

        $totalAmount = $validated['sent_amount'] + ($validated['fees'] ?? 0);
        $validated['user_id'] = auth();

        // تحقق من الرصيد
        if (!BalanceService::checkBalanceLimit(
            auth(),
            $validated['sent_currency'],
            $totalAmount,
            true
        )) {
            return response()->json(['error' => 'تجاوز الحد المسموح به للرصيد'], 422);
        }

        DB::beginTransaction();

        $friendRequest = FriendRequest::where(function ($query) use ($validated) {
                $query->where('sender_id', auth())
                      ->where('receiver_id', $validated['destination']);
            })
            ->orWhere(function ($query) use ($validated) {
                $query->where('receiver_id', auth())
                      ->where('sender_id', $validated['destination']);
            })
            ->where('status', 'accepted')
            ->lockForUpdate()
            ->firstOrFail();

        // تحقق من عدم إيقاف الحوالات
        if (
            ($friendRequest->sender_id == auth() && !$friendRequest->Stop_movements_2) ||
            ($friendRequest->receiver_id == auth() && !$friendRequest->Stop_movements_1)
        ) {
            throw new \Exception('تم إيقاف الحوالة. يرجى مراجعة المكتب.');
        }

        // تحديث رصيد الصداقة
        $currencyColumn1 = strtoupper($validated['sent_currency']) . '_1';
        $currencyColumn2 = strtoupper($validated['sent_currency']) . '_2';

        if ($friendRequest->sender_id == Auth::id() && $friendRequest->receiver_id == $validated['destination']) {
            $friendRequest->increment($currencyColumn1, $totalAmount);
            $friendRequest->decrement($currencyColumn2, $totalAmount);
        } elseif ($friendRequest->receiver_id == Auth::id() && $friendRequest->sender_id == $validated['destination']) {
            $friendRequest->increment($currencyColumn2, $totalAmount);
            $friendRequest->decrement($currencyColumn1, $totalAmount);
        }

        // تحديث الرصيد العام للمستخدم
        BalanceService::updateBalanceInUSD(
            $friendRequest,
            $validated['sent_currency'],
            $totalAmount,
            (auth() === $friendRequest->sender_id),
            $validated['destination']
        );

        // إنشاء الحوالة
        $transfer = Transfer::create([
            'recipient_name'    => $validated['recipient_name'],
            'recipient_mobile'  => $validated['recipient_mobile'],
            'destination'       => $validated['destination'],
            'sent_currency'     => $validated['sent_currency'],
            'sent_amount'       => $validated['sent_amount'],
            'received_currency' => $validated['received_currency'],
            'received_amount'   => $validated['received_amount'],
            'fees'              => $validated['fees'] ?? 0,
            'exchange_rate'     => $validated['exchange_rate'] ?? 1,
            'note'              => $validated['note'] ?? null,
            'user_id'           => auth(),
            'password'          => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT)
            // إذا كنتَ تحتاج حقلاً إضافيًا مثل movement_number ضعه هنا:
            // 'movement_number' => ...
        ]);



        // إنشاء صورة الحوالة
        $imageService = new GenerateTransferImageService();
        $imageData    = $imageService->generateTransferImage($transfer->id);

        DB::commit();

        return response()->json([
            'success'         => true,
            'transfer_id'     => $transfer->id,
            'movement_number' => $transfer->movement_number,
            'recipient_name'  => $transfer->recipient_name,
            'sent_amount'     => $transfer->sent_amount,
            'sent_currency' => ' (' . $transfer->currency->name_ar . ')',
            'password'        => $transfer->password,
            'destination'     => optional($transfer->destinationUser)->state_user . ' - ' . optional($transfer->destinationUser)->country_user,
            'Office_name'     => optional($transfer->destinationUser)->Office_name,
            'user_address'    => optional($transfer->destinationUser)->user_address,
            'image_data'      => $imageData,
            'message'         => 'تم إنشاء الحوالة بنجاح'
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['error' => 'خطأ في التحقق', 'details' => $e->errors()], 422);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['error' => 'الجهة غير موجودة'], 404);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Transfer Error: ' . $e->getMessage(), [
            'user'  => auth(),
            'trace' => $e->getTraceAsString()
        ]);
        event(new UndefinedErrorOccurred($e));
        return response()->json(['error' => 'فشل في المعاملة: ' . $e->getMessage()], 500);
    }
}

}
