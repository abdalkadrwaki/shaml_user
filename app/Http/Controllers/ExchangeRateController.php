<?php
// app/Http/Controllers/ExchangeRateController.php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index()
    {
        $rates = ExchangeRate::all();
        return view('exchange-rates', compact('rates'));
    }

    public function update(Request $request, $id)
    {
        $rate = ExchangeRate::findOrFail($id);
        $rate->update([
            'buy_rate' => $request->buy_rate,
            'sell_rate' => $request->sell_rate
        ]);

        return redirect()->back()->with('success', 'تم التحديث بنجاح');
    }
}
