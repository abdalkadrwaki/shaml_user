<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TransferForm extends Component
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
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.transfer-form');
    }
}
