<?php

namespace Webkul\Myfatoorah\Payment;

class Standard extends Myfatoorah
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code  = 'myfatoorah';

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
        return route('myfatoorah.standard.redirect');
    }
}
