<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function index()
    {
        $transfers = Transfer::with(['currency', 'recipient', 'receivedCurrency'])
        ->where('user_id', Auth::id())
        ->where('transaction_type', 'Transfer')
        ->where('status', '!=', 'Archived')
        ->orderBy('created_at', 'desc')
        ->paginate(13); // تقليل الحجم باستخدام الترقيم
        return view('deliveries.index', compact('transfers'));
    }

    public function show($id)
    {
        $transfer = Transfer::with(['recipient', 'deliveryProofs'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'transfer' => $transfer,
            'proofs' => $transfer->deliveryProofs
        ]);
    }
}
