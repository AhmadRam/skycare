<?php

namespace Webkul\Myfatoorah\Payment;

use Illuminate\Support\Facades\Storage;

class StandardApplePay extends Myfatoorah
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code  = 'applepay';

    /**
     * Line items fields mapping.
     *
     * @var array
     */
    protected $itemFieldsFormat = [
        'id'       => 'item_number_%d',
        'name'     => 'item_name_%d',
        'quantity' => 'quantity_%d',
        'price'    => 'amount_%d',
    ];

    /**
     * Return paypal redirect url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('myfatoorah.standard.redirect') . '?paymentMethodId=8';
    }

    /**
     * Get payment method image.
     *
     * @return array
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/applepay.png', 'shop');
    }
}
