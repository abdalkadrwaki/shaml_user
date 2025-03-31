<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TransferFormSyp extends Component
{
    public $destinations;
    public $currencies;
    public $exchangeRate;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $destinations
     * @param  mixed  $currencies
     * @param  mixed  $exchangeRate
     * @return void
     */
    public function __construct($destinations, $currencies, $exchangeRate = null)
    {
        $this->destinations = $destinations;
        $this->currencies   = $currencies;
        $this->exchangeRate = $exchangeRate;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.transfer-form-syp');
    }
}
