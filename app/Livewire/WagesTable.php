<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Wages;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class WagesTable extends Component
{
    public $wages;
    public $selectedCurrency = null;
    public $currencies;
    public $encryptedId;

    // لحفظ السجل الذي يتم تعديله
    public $editWageId = null;
    public $fromAmount;
    public $toAmount;
    public $fee;
    public $currency_id;

    public function mount($id = null)
    {
        if ($id) {
            $this->encryptedId = Crypt::decryptString($id);
        }

        $this->currencies = Currency::all();
        $this->filterData();
    }

    public function updatedSelectedCurrency()
    {
        $this->filterData();
    }

    public function filterData()
    {
        $userId = Auth::id();
        $query = Wages::with('currency');

        if ($this->selectedCurrency) {
            $query->where('currency_id', $this->selectedCurrency);
        }

        $query->where('user_id_1', $userId);

        if ($this->encryptedId) {
            $query->where('user_id_2', $this->encryptedId);
        }

        $this->wages = $query->get();
    }

    // لتفعيل وضع التعديل
    public function edit($wageId)
    {
        $wage = Wages::find($wageId);
        $this->editWageId = $wageId;
        $this->fromAmount = $wage->from_amount;
        $this->toAmount = $wage->to_amount;
        $this->fee = $wage->fee;
        $this->currency_id = $wage->currency_id;
    }

    // لتحديث السجل المعدل
    public function update()
    {
        $wage = Wages::find($this->editWageId);

        $wage->from_amount = $this->fromAmount;
        $wage->to_amount = $this->toAmount;
        $wage->fee = $this->fee;
        $wage->currency_id = $this->currency_id;

        $wage->save();

        $this->resetEditFields();  // إعادة تعيين الحقول بعد التحديث
        $this->filterData(); // تحديث البيانات المعروضة
    }

    // لإعادة تعيين الحقول بعد التعديل// هذه الدالة ستقوم بإعادة تعيين الحقول بعد التعديل
    public function resetEditFields()
    {
        $this->reset(['editWageId', 'fromAmount', 'toAmount', 'fee', 'currency_id']);
    }



    public function render()
    {
        return view('livewire.wages-table');
    }
}
