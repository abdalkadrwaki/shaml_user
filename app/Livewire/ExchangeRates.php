<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ExchangeRate;

class ExchangeRates extends Component
{
    public $exchangeRates, $currency_pair, $name_ar, $buy_rate, $sell_rate, $exchangeRateId;

    protected $rules = [
        'currency_pair' => 'required|string|unique:exchange_rates,currency_pair',
        'name_ar' => 'required|string',
        'buy_rate' => 'required|numeric',
        'sell_rate' => 'required|numeric',
    ];


    public function mount()
    {
        $this->exchangeRates = ExchangeRate::all();
    }

    public function render()
    {
        return view('livewire.exchange-rates');
    }

    public function store()
    {
        $this->validate();

        ExchangeRate::create([
            'currency_pair' => $this->currency_pair,
            'name_ar' => $this->name_ar,
            'buy_rate' => $this->buy_rate,
            'sell_rate' => $this->sell_rate,
        ]);

        session()->flash('message', 'تم إضافة العملة بنجاح!');
        $this->resetInputFields();
        $this->mount(); // إعادة تحميل البيانات
    }

    public function edit($id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        $this->exchangeRateId = $exchangeRate->id;
        $this->currency_pair = $exchangeRate->currency_pair;
        $this->name_ar = $exchangeRate->name_ar;
        $this->buy_rate = $exchangeRate->buy_rate;
        $this->sell_rate = $exchangeRate->sell_rate;
    }


    public function update()
    {
        // التحقق من صحة البيانات مع تجاهل التحقق من الفريدة عند تعديل السجل نفسه
        $this->validate([
            'currency_pair' => 'required|string|unique:exchange_rates,currency_pair,' . $this->exchangeRateId,  // تأكد من أنك تستثني الـ ID الخاص بالسجل نفسه
            'name_ar' => 'required|string',
            'buy_rate' => 'required|numeric',
            'sell_rate' => 'required|numeric',
        ]);

        // إيجاد السجل وتحديثه
        $exchangeRate = ExchangeRate::findOrFail($this->exchangeRateId);
        $exchangeRate->update([
            'currency_pair' => $this->currency_pair,
            'name_ar' => $this->name_ar,
            'buy_rate' => $this->buy_rate,
            'sell_rate' => $this->sell_rate,
        ]);

        session()->flash('message', 'تم تعديل العملة بنجاح!');
        $this->resetInputFields();
        $this->mount(); // إعادة تحميل البيانات
    }

    public function delete($id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        $exchangeRate->delete();

        session()->flash('message', 'تم حذف العملة بنجاح!');
        $this->mount(); // إعادة تحميل البيانات
    }

    public function resetInputFields()
    {
        $this->currency_pair = '';
        $this->name_ar = '';
        $this->buy_rate = '';
        $this->sell_rate = '';
        $this->exchangeRateId = null;
    }

}
