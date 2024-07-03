<?php

namespace Webkul\Payment;

use Illuminate\Support\Facades\Config;
use Jenssegers\Agent\Agent;
use Webkul\Checkout\Facades\Cart;

class Payment
{
    /**
     * Returns all supported payment methods
     *
     * @return array
     */
    public function getSupportedPaymentMethods()
    {
        return [
            'payment_methods'  => $this->getPaymentMethods(),
        ];
    }

    /**
     * Returns all supported payment methods
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        $cart = Cart::getCart();

        $paymentMethods = [];

        foreach (Config::get('payment_methods') as $paymentMethodConfig) {
            $paymentMethod = app($paymentMethodConfig['class']);

            if ($paymentMethod->isAvailable()) {
                $paymentMethods[] = [
                    'method'       => $paymentMethod->getCode(),
                    'method_title' => $paymentMethod->getTitle(),
                    'description'  => $paymentMethod->getDescription(),
                    'sort'         => $paymentMethod->getSortOrder(),
                    'image'        => $paymentMethod->getImage(),
                ];
            }
        }

        if (isset($cart->billing_address) && $cart->billing_address->country != "KW") {
            // unset($paymentMethods['myfatoorah']);
            unset($paymentMethods['cashondelivery']);
        }

        // $agent = new Agent();

        // if ($agent->browser()) {
        //     if ($agent->isDesktop()) {
        //         unset($paymentMethods['applepay']);
        //         unset($paymentMethods['googlepay']);
        //     } else if ($agent->is('iPhone') || $agent->is('iPad') || $agent->is('iPod') || $agent->is('Macintosh') || $agent->is('Mac OS')) {
        //         unset($paymentMethods['googlepay']);
        //     } else {
        //         unset($paymentMethods['applepay']);
        //     }
        // }

        // $paymentMethods = array_values($paymentMethods);

        usort($paymentMethods, function ($a, $b) {
            if ($a['sort'] == $b['sort']) {
                return 0;
            }

            return ($a['sort'] < $b['sort']) ? -1 : 1;
        });

        return $paymentMethods;
    }

    /**
     * Returns payment redirect url if have any
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return string
     */
    public function getRedirectUrl($cart)
    {
        $payment = app(Config::get('payment_methods.' . $cart->payment->method . '.class'));

        return $payment->getRedirectUrl();
    }

    /**
     * Returns payment method additional information
     *
     * @param  string  $code
     * @return array
     */
    public static function getAdditionalDetails($code)
    {
        $paymentMethodClass = app(Config::get('payment_methods.' . $code . '.class'));

        return $paymentMethodClass->getAdditionalDetails();
    }
}
