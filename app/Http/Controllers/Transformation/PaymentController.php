<?php

namespace App\Http\Controllers\Transformation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;
use App\Models\Transfer;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{





    /**
     * عرض صفحة الحوالات.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // جلب العملة والتاريخ من الطلب
        $currency = $request->input('currency');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // جلب الحوالات الصادرة (التي أرسلها المستخدم الحالي)
        $outgoingTransfers = Transfer::where('user_id', Auth::id())
            ->when($currency, function ($query) use ($currency) {
                return $query->where('sent_currency', $currency);
            })
            ->when($startDate, function ($query) use ($startDate) {
                return $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                return $query->whereDate('created_at', '<=', $endDate);
            })
            ->get();

        // جلب الحوالات الواردة (التي تلقاها المستخدم الحالي)
        $incomingTransfers = Transfer::where('destination', Auth::id())
            ->when($currency, function ($query) use ($currency) {
                return $query->where('received_currency', $currency);
            })
            ->when($startDate, function ($query) use ($startDate) {
                return $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                return $query->whereDate('created_at', '<=', $endDate);
            })
            ->get();

        // حساب التراكمي (مجموع الحوالات الواردة ناقص مجموع الحوالات الصادرة)
        $totalIncoming = $incomingTransfers->sum('received_amount');
        $totalOutgoing = $outgoingTransfers->sum('sent_amount');
        $cumulative = $totalIncoming - $totalOutgoing;

        // جلب العملات النشطة لعنصر select
        $currencies = Currency::activeCurrencies();

        // إرجاع الصفحة مع البيانات
        return view('transfers.index', compact('outgoingTransfers', 'incomingTransfers', 'cumulative', 'currencies', 'currency', 'startDate', 'endDate'));
    }
}
