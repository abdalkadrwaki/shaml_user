<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TransferFormExchange extends Component
{
    public $currencies;
    public $destinations;

    public function __construct($currencies, $destinations)
    {
        $this->currencies = $currencies;
        $this->destinations = $destinations;
    }

    public function render(): View|string
    {
        return view('components.transfer-form-exchange');
    }
}
