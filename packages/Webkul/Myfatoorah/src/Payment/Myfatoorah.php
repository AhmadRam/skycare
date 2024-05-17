<?php

namespace Webkul\Myfatoorah\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

abstract class Myfatoorah extends Payment
{
    /**
     * Format a currency value according to paypal's api constraints
     *
     * @param float|int $long
     * @return float
     */
    public function formatCurrencyValue($number): float
    {
        return round((float) $number, 2);
    }

    /**
     * Format phone field according to paypal's api constraints
     *
     * Strips non-numbers characters like '+' or ' ' in
     * inputs like "+54 11 3323 2323"
     *
     * @param mixed $phone
     * @return string
     */
    public function formatPhone($phone): string
    {
        return preg_replace('/[^0-9]/', '', (string) $phone);
    }


    /**
     * Get payment method image.
     *
     * @return array
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/myfatoorah.png', 'shop');
    }
}
