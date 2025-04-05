<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function index()
    {
        $transfers = Transfer::with(['currency', 'recipient'])
            ->where('user_id', Auth::id())
            ->where('status', 'Delivered')
            ->orderBy('delivered_at', 'desc')
            ->paginate(15);

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
