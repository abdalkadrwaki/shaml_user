<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TransferFormExchange extends Component
{
    public $currencies;
    public $destinations;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $currencies
     * @param  mixed  $destinations
     * @return void
     */
    public function __construct($currencies, $destinations)
    {
        $this->currencies = $currencies;
        $this->destinations = $destinations;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.transfer-form-exchange');
    }
}
