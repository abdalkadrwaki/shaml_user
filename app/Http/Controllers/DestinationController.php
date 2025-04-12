<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wages;
use App\Models\FriendRequest;
use App\Models\Currency; // موديل `currencies`
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;

class DestinationController extends Controller
{

    public function index()
    {
        $currentUserId = Auth::id();
        $usdFilter = request()->input('usd_filter', 'all');
        $currencyFilter = request()->input('currency_filter', 'all');

        // جلب طلبات الصداقة المقبولة أولاً بدون تصفية
        $friendRequests = FriendRequest::where(function ($query) use ($currentUserId) {
            $query->where('receiver_id', $currentUserId)
                ->orWhere('sender_id', $currentUserId);
        })
        ->where('status', 'accepted')
        ->get();

        // تطبيق التصفيات على البيانات
        if ($usdFilter !== 'all') {
            $friendRequests = $friendRequests->filter(function ($request) use ($currentUserId, $usdFilter) {
                $balance = $request->receiver_id === $currentUserId
                    ? $request->balance_in_usd_1
                    : $request->balance_in_usd_2;
                return $usdFilter === 'positive' ? $balance > 0 : $balance < 0;
            });
        }

        if ($currencyFilter !== 'all') {
            $friendRequests = $friendRequests->filter(function ($request) use ($currentUserId, $currencyFilter) {
                foreach (Currency::pluck('name_en') as $currency) {
                    $columnKey = $request->receiver_id === $currentUserId
                        ? $currency . '_1'
                        : $currency . '_2';
                    $balance = $request->{$columnKey} ?? 0;
                    if (($currencyFilter === 'positive' && $balance > 0) ||
                        ($currencyFilter === 'negative' && $balance < 0)) {
                        return true;
                    }
                }
                return false;
            });
        }

        // استخراج الـ IDs بعد التصفية
        $userIds = $friendRequests->map(function ($request) use ($currentUserId) {
            return $request->receiver_id === $currentUserId ? $request->sender_id : $request->receiver_id;
        })->unique();

        // جلب بيانات المكاتب بناءً على الـ IDs المصفاة
        $destinations = User::whereIn('id', $userIds)
            ->get(['id', 'Office_name', 'state_user', 'country_user']);

        // جلب بيانات العملات
        $currencies = Currency::all();
        $currencyNames = $currencies->pluck('name_ar', 'name_en');
        $columns = $currencies->pluck('name_en')->map(function ($currency) {
            return [
                'sender_column' => $currency . '_2',
                'receiver_column' => $currency . '_1',
            ];
        });

        return view('destination.index', compact('destinations', 'friendRequests', 'columns', 'currencyNames'));
    }
    public function updateLimited(Request $request, $id)
    {
        $currentUserId = Auth::id();

        // جلب طلب الصداقة باستخدام ID
        $friendRequest = FriendRequest::findOrFail($id);

        // التحقق من أن المستخدم هو إما المرسل أو المستقبل
        if ($friendRequest->sender_id !== $currentUserId && $friendRequest->receiver_id !== $currentUserId) {
            return response()->json(['error' => 'لا يمكنك تعديل هذا المبلغ.']);
        }

        // التحقق من المبلغ المدخل
        $validatedData = $request->validate([
            'limited' => 'required|numeric|min:0', // تأكيد أن المبلغ هو عدد إيجابي
        ]);

        // تعديل المبلغ بناءً على دور المستخدم
        if ($friendRequest->sender_id === $currentUserId) {
            // إذا كان المستخدم هو المرسل
            $friendRequest->Limited_1 = $validatedData['limited'];
        } elseif ($friendRequest->receiver_id === $currentUserId) {
            // إذا كان المستخدم هو المستقبل
            $friendRequest->Limited_2 = $validatedData['limited'];
        }

        // حفظ التغييرات في قاعدة البيانات
        $friendRequest->save();

        // إعادة استجابة AJAX مع النجاح
        return response()->json(['success' => true]);
    }




    public function updatePassword(Request $request, $id)
    {
        $currentUserId = Auth::id();

        // جلب الطلب
        $friendRequest = FriendRequest::findOrFail($id);

        // التحقق من أن المستخدم هو المرسل أو المستقبل
        if ($friendRequest->sender_id !== $currentUserId && $friendRequest->receiver_id !== $currentUserId) {
            return response()->json(['error' => 'لا يمكنك تعديل هذه البيانات.']);
        }

        // التحقق من صحة البيانات المدخلة
        $validatedData = $request->validate([
            'password' => 'required|string|min:6', // كلمة المرور يجب أن تكون على الأقل 6 أحرف
        ]);

        // تحديث كلمة المرور بناءً على دور المستخدم
        if ($friendRequest->sender_id === $currentUserId) {
            $friendRequest->password_usd_1 = $validatedData['password'];
        } elseif ($friendRequest->receiver_id === $currentUserId) {
            $friendRequest->password_usd_2 = $validatedData['password'];
        }

        $friendRequest->save();

        return response()->json(['success' => true]);
    }

    public function toggleStopMovements(Request $request, $id)
    {
        $currentUserId = Auth::id();

        // جلب طلب الصداقة باستخدام ID
        $friendRequest = FriendRequest::findOrFail($id);

        // التحقق من أن المستخدم هو المرسل أو المستقبل
        if ($friendRequest->sender_id !== $currentUserId && $friendRequest->receiver_id !== $currentUserId) {
            return response()->json(['error' => 'لا يمكنك تعديل هذه الحركة.']);
        }

        // الحصول على الحقل المراد تعديله
        $field = $request->input('field');

        // التحقق من أن الحقل موجود في الجدول
        $allowedFields = [
            'stop_approval_1',
            'stop_approval_2',
            'stop_exchange_1',
            'stop_exchange_2',
            'hide_account_1',
            'hide_account_2',
            'stop_link_1',
            'stop_link_2',
            'Stop_movements_1',
            'Stop_movements_2',
            'stop_syp_1',
            'stop_syp_2',
            'Slice_type_1',
            'Slice_type_2',

        ];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['error' => 'الحقل غير صالح.']);
        }

        // تغيير حالة الحقل
        $friendRequest->$field = !$friendRequest->$field;

        // حفظ التغييرات في قاعدة البيانات
        $friendRequest->save();

        // إعادة استجابة AJAX مع النجاح
        return response()->json([
            'success' => true,
            $field => $friendRequest->$field,
        ]);
    }



    /**
     * عرض صفحة الأجور لوجهة معينة.
     *
     * @param string $id (ID مشفر)
     * @return \Illuminate\View\View
     */
    public function wages($id)
    {
        // فك تشفير الـ ID
        $decodedId = Crypt::decrypt($id);

        // جلب البيانات الخاصة بالجهة باستخدام الـ ID المفكوك
        $destination = User::findOrFail($decodedId);

        $currencies = Currency::activeCurrencies();


        // تمرير البيانات إلى صفحة العرض
        return view('destination.wages', compact('destination', 'currencies'));
    }
    public function filterWages(Request $request, $id)
    {
        try {
            $decodedId = Crypt::decrypt($id);
            $destination = User::findOrFail($decodedId);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid ID or Decryption failed'], 400);
        }

        $userId = Auth::id();  // تم تعديل اسم المتغير ليكون باللغة الإنجليزية

        // تحقق من وجود عملة
        $currencyId = $request->input('currency_id');

        // تصفية الأجور حسب العملة أو المستخدمين
        $wagesQuery = Wages::query();

        if ($currencyId) {
            $wagesQuery->where('currency_id', $currencyId);
        }

        // تحقق من الشرطين معًا
        $wagesQuery->where(function ($query) use ($userId, $destination) {
            $query->where('user_id_1', $userId)
                ->where('user_id_2', $destination->id); // هنا يتم التحقق من كلا الشرطين معًا
        });

        // تنفيذ الاستعلام
        $wages = $wagesQuery->get();

        // بناء الاستجابة بتنسيق JSON
        $response = $wages->map(function ($wage) {
            return [
                'type' => $wage->type == 1 ? 'أجور ثابتة' : 'نسبة مئوية',
                'from_amount' => $wage->from_amount,
                'to_amount' => $wage->to_amount,
                'fee' => $wage->fee,
                'currency_name_ar' => $wage->currency->name_ar,
                'created_at' => $wage->created_at ? $wage->created_at->format('Y-m-d H:i') : 'غير محدد',
            ];
        });

        return response()->json(['wages' => $response]);
    }
}
