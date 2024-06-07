<?php

namespace Webkul\Myfatoorah\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\Checkout\Models\Cart as ModelsCart;
use Webkul\Customer\Models\RegisterDevice;
use Webkul\Customer\Repositories\CustomerActivityRepository;
use Webkul\Sales\Repositories\InvoiceRepository;

class StandardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Attribute\Repositories\OrderRepository  $orderRepository
     * @param  \Webkul\Sales\Repositories\InvoiceRepository  $invoiceRepository
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository,
    ) {
    }

    /**
     * Redirects to the myfatoorah.
     *
     * @return \Illuminate\View\View
     */
    public function redirect()
    {
        $cart_id = request()->cart_id ?? null;
        $cart = Cart::getCart($cart_id);

        $billingAddress = $cart->getBillingAddressAttribute();
        $prepareDataForOrder = Cart::prepareDataForOrder($cart->id);

        $register_device_id = request()->register_device_id ?? null;
        if ($register_device_id) {
            $register_device = RegisterDevice::find($register_device_id);

            if ($register_device) {
                $prepareDataForOrder['register_device_id'] = $register_device_id;
                $prepareDataForOrder['customer_id'] = $prepareDataForOrder['customer_id'] ?? $register_device->customer_id ?? null;
            }
        }

        $order = $this->orderRepository->where('cart_id', $cart->id)->first();

        if ($order) {
            $this->orderRepository->delete($order->id);
        }

        $prepareDataForOrder['status'] = 'no_status';
        $order = $this->orderRepository->create($prepareDataForOrder);

        $data = [
            'paymentMethodId'    => request()->paymentMethodId,
            'CustomerName'       => "$billingAddress->first_name $billingAddress->last_name",
            'InvoiceValue'       => $cart->grand_total,
            'DisplayCurrencyIso' => $cart->cart_currency_code,
            'CustomerEmail'      => $billingAddress->email,
            'CallBackUrl'        => route('myfatoorah.standard.callback') . "?register_device_id=" . $register_device_id . '&order_id=' . $order->id . '&cart_id=' . $cart->id,
            'ErrorUrl'           => route('myfatoorah.standard.cancel') . "?register_device_id=" . $register_device_id . '&order_id=' . $order->id,
            'MobileCountryCode'  => '',
            'CustomerMobile'     => $billingAddress->phone,
            'Language'           => app()->getLocale(),
            'CustomerReference'  => $cart->id,
            'SourceInfo'         => 'SkyCare'
        ];

        ini_set('precision', 14);
        ini_set('serialize_precision', -1);

        if (core()->getConfigData('myfatoorah.payment_methods.myfatoorah.sandbox') == 0) {
            $url = 'https://api.myfatoorah.com/v2/ExecutePayment';
            $apiKey = 'Bearer ' . core()->getConfigData('myfatoorah.payment_methods.myfatoorah.api_key');
        } else {
            $url = 'https://apitest.myfatoorah.com/v2/ExecutePayment';
            $apiKey = 'Bearer ' . core()->getConfigData('myfatoorah.payment_methods.myfatoorah.api_test_key');
        }

        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => ["Authorization: $apiKey", 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true
        ));

        $res = curl_exec($curl);

        $curlError = curl_error($curl);

        curl_close($curl);

        $json = json_decode((string) $res);
        if ($curlError) {
            session()->flash('error', $curlError);

            return redirect()->route('shop.checkout.cart.index');
        } else {

            if ($json->IsSuccess == false) {
                session()->flash('error', $json->ValidationErrors[0]->Error);

                return redirect()->route('shop.checkout.cart.index');
            }

            // app(CustomerActivityRepository::class)->create([
            //     'note' => 'قام بفتح صفحة الدفع للسلة رقم  ' . $cart->id,
            //     'ip' => request()->ip(),
            //     'customer_name' =>  $cart->customer_first_name . ' ' .  $cart->customer_last_name,
            //     'customer_id' =>  $cart->customer_id ?? null,
            // ]);

            $redirectUrl = $json->Data->PaymentURL;

            return redirect($redirectUrl);
        }
    }

    /**
     * Cancel payment from myfatoorah.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel()
    {
        session()->flash('error', trans('shop::app.checkout.cart.myfatoorah-payment-canceled'));

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * callback payment.
     *
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
        if (!empty($request->input('paymentId')) && !empty($request->input('Id'))) {
            // $prepareDataForOrder = Cart::prepareDataForOrder();

            // if ($register_device_id = request()->register_device_id) {
            //     $prepareDataForOrder['register_device_id'] = $register_device_id;
            //     $register_device = RegisterDevice::find($register_device_id);
            //     $prepareDataForOrder['customer_id'] = $prepareDataForOrder['customer_id'] ?? $register_device->customer_id;
            // }

            // $order = $this->orderRepository->create($prepareDataForOrder);
            $order = $this->orderRepository->find(request()->order_id);
            if ($order->status == 'no_status') {
                $this->orderRepository->update(['status' => 'processing'], $order->id);

                if ($order->canInvoice()) {
                    $this->invoiceRepository->create($this->prepareInvoiceData($order));
                }

                Cart::deActivateCart();

                Cart::activateCartIfSessionHasDeactivatedCartId();

                // app(CustomerActivityRepository::class)->create([
                //     'note' => 'قام بإنشاء طلب جديد رقم  ' . $order->id,
                //     'ip' => request()->ip(),
                //     'customer_name' =>  $order->customer_first_name . ' ' .  $order->customer_last_name,
                //     'customer_id' =>  $order->customer_id ?? null,
                // ]);

                // if (isset($register_device) && $register_device->os != 'web') {
                // } else {
                // }
            }

            Event::dispatch('checkout.order.save.after', $order);

            session()->flash('order', $order);

            return redirect()->route('shop.checkout.onepage.success');
        }

        session()->flash('error', 'Something went wrong in payment processing, Please try again.');

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Success payment.
     *
     * @return \Illuminate\Http\Response
     */
    public function success()
    {
        $order = $this->orderRepository->create(Cart::prepareDataForOrder());

        Cart::deActivateCart();

        session()->flash('order', $order);

        return redirect()->route('shop.checkout.success');
    }

    /**
     * Prepares order's invoice data for creation.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = ["order_id" => $order->id,];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}
