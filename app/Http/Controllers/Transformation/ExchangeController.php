<?php

namespace App\Http\Controllers\Transformation;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\User;
use App\Models\Transfer;
use App\Models\FriendRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Services\BalanceService;
use App\Services\FriendService;

class ExchangeController extends Controller
{
    public function create()
    {
      

        // تحميل العملات النشطة من قاعدة البيانات
        $currencies = Currency::activeCurrencies();

        // تحميل الأصدقاء المسموح لهم باستلام الحوالات
        $destinations = FriendService::loadDestinations();

        return view('transfer.exchange', compact('currencies'));
    }

    public function getBalance(Request $request)
    {
        $userId = Auth::id();
        $currency = $request->currency; // العملة المطلوبة
        $destinationId = $request->destination_exchange; // الجهة المحددة

        try {
            // التحقق من العلاقة بين المستخدم والجهة المحددة
            $friendRequest = FriendRequest::where(function ($query) use ($userId, $destinationId) {
                $query->where(function ($q) use ($userId, $destinationId) {
                    $q->where('sender_id', $userId)
                        ->where('receiver_id', $destinationId);
                })->orWhere(function ($q) use ($userId, $destinationId) {
                    $q->where('receiver_id', $userId)
                        ->where('sender_id', $destinationId);
                });
            })->first();

            if (!$friendRequest) {
                throw new \Exception('لا يوجد طلب صداقة بينك وبين الجهة المحددة.');
            }

            // تحديد العمود الصحيح بناءً على العلاقة
            $balanceColumn = null;
            if ($friendRequest->sender_id == $userId && $friendRequest->receiver_id == $destinationId) {
                $balanceColumn = "{$currency}_2";
            } elseif ($friendRequest->receiver_id == $userId && $friendRequest->sender_id == $destinationId) {
                $balanceColumn = "{$currency}_1";
            }

            if (!$balanceColumn || !isset($friendRequest->$balanceColumn)) {
                throw new \Exception("لم يتم العثور على رصيد في العمود: {$balanceColumn}.");
            }

            // جلب الرصيد المتاح
            $userBalance = $friendRequest->$balanceColumn;

            // التأكد من أن الرصيد أكبر من صفر
            if ($userBalance <= 0) {
                return response()->json(['error' => 'رصيدك في العملة المحددة غير كافٍ.']);
            }

            return response()->json(['balance' => $userBalance]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }


    public function storeExchange(Request $request)
    {
        $userId = Auth::id();
        $activeCurrencyNames = Currency::activeCurrencies()->pluck('name_en')->toArray();
        try {
            // التحقق من صحة المدخلات
            // في دالة storeExchange


            $validated = $request->validate([
                'sent_currency'       => 'required|string|in:' . implode(',', $activeCurrencyNames),
                'sent_amount'         => 'required|numeric|min:0.01',
                'received_currency'   => 'required|string|in:' . implode(',', $activeCurrencyNames),
                'received_amount'     => 'required|numeric|min:0.01',
                'exchange_rate'       => 'nullable|numeric|min:0',
                'note'                => 'nullable|string|max:500',
                'destination_exchange'=> 'required|exists:users,id',
            ]);



            // منع إرسال الحوالة لنفس المستخدم
            if ($validated['destination_exchange'] == $userId) {
                throw new \Exception('لا يمكنك إرسال حوالة لنفسك.');
            }
            if ($validated['sent_currency'] == $validated['received_currency']) {
                throw new \Exception('لا يمكنك ارسال سند صرافة لأن العملة نفسها  .');
            }

            // إضافة بيانات المستخدم إلى المدخلات
            $validated['user_id'] = $userId;
            $validated['recipient_name'] = 'صرافة';
            $validated['transaction_type'] = 'Exchange';
            $validated['recipient_mobile'] = '0000000000';
            $validated['destination'] = $validated['destination_exchange'];

            $totalAmount = $validated['sent_amount'];
            $isSender = true;

            // التحقق من أن الرصيد يكفي لإتمام العملية
            if (!BalanceService::checkBalanceLimit($userId, $validated['sent_currency'], $totalAmount, $isSender)) {
                throw new \Exception('تم تجاوز الحد المسموح به من الرصيد.');
            }

            DB::transaction(function () use ($validated, $userId) {
                // التحقق من وجود علاقة صداقة مقبولة بين المرسل والمستقبل
                $friendRequest = FriendService::checkAcceptedFriendship($userId, $validated['destination_exchange']);

                if (!$friendRequest) {
                    throw new \Exception('لا يمكن إتمام العملية. يجب أن تكون هناك علاقة صداقة مقبولة بينك وبين المستلم.');
                }

                // التحقق من إمكانية إتمام العملية بناءً على حالة الصداقة
                if ($friendRequest->sender_id == $userId && !$friendRequest->stop_exchange_2) {
                    throw new \Exception('تم إيقاف الحوالة. يرجى مراجعة المكتب.');
                } elseif ($friendRequest->receiver_id == $userId && !$friendRequest->stop_exchange_1) {
                    throw new \Exception('تم إيقاف الحوالة. يرجى مراجعة المكتب.');
                }

                // تحديد العمود المناسب بناءً على المستخدم (مرسل أو مستقبل)
                $balanceColumn = $friendRequest->sender_id == $userId
                    ? "{$validated['sent_currency']}_2"  // إذا كان المرسل
                    : ($friendRequest->receiver_id == $userId ? "{$validated['sent_currency']}_1" : null); // إذا كان المستقبل

                // التأكد من وجود العمود قبل استخدامه
                if (!$balanceColumn || !isset($friendRequest->$balanceColumn)) {
                    throw new \Exception("لم يتم العثور على رصيد في العمود: {$balanceColumn}.");
                }

                // الحصول على رصيد المستخدم
                $userBalance = $friendRequest->$balanceColumn;

                // التحقق من أن الرصيد كافٍ لإتمام العملية
                if ($validated['sent_amount'] > $userBalance) {
                    throw new \Exception("رصيدك من {$validated['sent_currency']} غير كافٍ لإتمام الحوالة.");
                }

                // حفظ بيانات الحوالة
                Transfer::create($validated);
            });

            return redirect()->route('dashboard')
                ->with('exchange', $validated);
        } catch (\Exception $e) {
            // تسجيل الأخطاء في السجلات
            Log::error('خطأ أثناء معالجة الحوالة:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'data' => $request->all(),
            ]);

            // إرجاع الخطأ مع الرسالة
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput()
                ->without('exchange');
        }
    }
}
