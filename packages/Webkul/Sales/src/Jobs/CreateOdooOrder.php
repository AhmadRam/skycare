<?php

namespace Webkul\Sales\Jobs;

use Consilience\OdooApi\OdooService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Core\Models\CountryStateCity;
use Webkul\Sales\Models\Order;

class CreateOdooOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job should be attempted.
     *
     * @var int
     */
    public $tries = 10; // Number of retries

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 60; // Time in seconds


    /**
     *
     * @var int
     */
    protected $order_id;

    /**
     * Create a new job instance.
     *
     * @param  array  $sku
     * @return void
     */
    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = Order::find($this->order_id);
        // dd($order);
        $odooClient = new OdooService();
        $client = $odooClient->getClient();

        $odoo_order = $client->search('sale.order', [['origin', '=', '#' . $order->increment_id]])->first();

        if ($odoo_order == null) {

            $address = $order->shipping_address;
            if ($order->customer_id != null) {
                $odoo_customer = $client->search('res.partner', [['ref', '=', $order->customer_id]])->first();
            }

            if (!isset($odoo_customer)) {
                $odoo_customer = $client->search('res.partner', [['phone', '=', $address->phone_code . '' . $address->phone]])->first();
            }

            $odoo_country = $client->search('res.country', [['code', '=', $address->country]])->first();

            // $state = CountryState::find($address->state_id);
            // if ($state) {
            //     $odoo_state = $client->search('res.country.state', [['name', '=', $state->translate('en')['default_name']]])->first();
            //     // if (!$odoo_state) {
            //     //     $odoo_state = $client->create('res.country.state', [
            //     //         'name' => $state->translate('en')['default_name'],
            //     //         'country_id' => $odoo_country,
            //     //         'code' => (int) $address->state_id,
            //     //         // 'code' => substr($address->state, 0, 2) . '_' . $address->country,
            //     //         'display_name' => $state->translate('en')['default_name'],
            //     //     ]);
            //     // }
            // }


            // $city = CountryStateCity::where('default_name', $address->city)->first();

            // if ($city) {
            // $odoo_city = $client->search('res.city', [['name', '=', $city->translate('en')['default_name']]])->first();
            // if (!$odoo_city) {
            //     $odoo_city = $client->create('res.city', [
            //         'name' => $city->translate('en')['default_name'],
            //         'country_id' => $odoo_country,
            //         'state_id' => $odoo_state ?? null,
            //         'display_name' => $city->translate('en')['default_name'],
            //     ]);
            // }
            // }


            // if (!isset($odoo_customer)) {
            $address1 = explode(PHP_EOL, $address->address1);
            $odoo_customer_data = [
                'name' => $address->first_name . ' ' . $address->last_name,
                'email' => $address->email,
                'phone' => $address->phone_code . '' . $address->phone,
                'type' => 'contact',
                'ref' => $address->customer_id,
                'company_type' => 'person',
                'zip' => $address->postcode,
                'country_id' => $odoo_country,
                'city' => $address->city ?? null,
                'street' => (isset($address1[0]) && $address1[0] != 0 ? ('block : ' . $address1[0] . ' - ') : null) . (isset($address1[1]) ? ('street : ' . $address1[1] . ' - ') : null) .  ((isset($address1[2]) ? 'House No:' . $address1[2] : '') . (isset($address1[3]) ? (' / Floor: ' . $address1[3]) : '') . (isset($address1[4]) ? (' / Flat: ' . $address1[4]) : '')) . ($address1[5] ?? null),
                'street2' => $address->address2,
                'comment' => $address->note,
                // 'state_id' =>  $odoo_state ?? null,
                // 'city_id' => $odoo_city ?? null,
                // 'block' => isset($address1[0]) && $address1[0] != 0 ? $address1[0] : null,
                // 'street' => $address1[1] ?? null,
                // 'house' => (isset($address1[2]) ? 'House No:' . $address1[2] : '') . (isset($address1[3]) ? (' / Floor: ' . $address1[3]) : '') . (isset($address1[4]) ? (' / Flat: ' . $address1[4]) : ''),
                // 'avenue' => $address1[5] ?? null,
            ];

            if ($odoo_customer) {
                $client->write('res.partner', $odoo_customer, $odoo_customer_data);
            } else {
                $odoo_customer = $client->create('res.partner', $odoo_customer_data);
            }
            // }

            $odoo_address = $client->search('res.partner', [['ref', '=', $address->id], ['parent_id', '=', $odoo_customer]])->first();

            if (!$odoo_address) {
                $odoo_address = $client->search('res.partner', [['comment', '=', $address->id], ['parent_id', '=', $odoo_customer]])->first();
            }

            // if (!$odoo_address) {
            $address1 = explode(PHP_EOL, $address->address1);
            $odoo_address_data = [
                'name' => $address->first_name . ' ' . $address->last_name,
                'email' => $address->email,
                'phone' => $address->phone_code . '' . $address->phone,
                'ref' => $address->id,
                'type' => 'delivery',
                'parent_id' => $odoo_customer,
                'zip' => $address->postcode,
                'country_id' => $odoo_country,
                'city' => $address->city ?? null,
                'street' => (isset($address1[0]) && $address1[0] != 0 ? ('block : ' . $address1[0] . ' - ') : null) . (isset($address1[1]) ? ('street : ' . $address1[1] . ' - ') : null) .  ((isset($address1[2]) ? 'House No:' . $address1[2] : '') . (isset($address1[3]) ? (' / Floor: ' . $address1[3]) : '') . (isset($address1[4]) ? (' / Flat: ' . $address1[4]) : '')) . ($address1[5] ?? null),
                'street2' => $address->address2,
                'comment' => $address->note,
                // 'state_id' =>  $odoo_state ?? null,
                // 'city_id' => $odoo_city ?? null,
                // 'block' => isset($address1[0]) && $address1[0] != 0 ? $address1[0] : null,
                // 'street' => $address1[1] ?? null,
                // 'house' => (isset($address1[2]) ? 'House No:' . $address1[2] : '') . (isset($address1[3]) ? (' / Floor: ' . $address1[3]) : '') . (isset($address1[4]) ? (' / Flat: ' . $address1[4]) : ''),
                // 'avenue' => $address1[5] ?? null,
            ];

            if ($odoo_address) {
                $client->write('res.partner', $odoo_address, $odoo_address_data);
            } else {
                $odoo_address = $client->create('res.partner', $odoo_address_data);
            }
            // }

            $odoo_data = [
                'partner_id' => $odoo_address,
                'partner_invoice_id' => $odoo_address,
                'partner_shipping_id' => $odoo_address,
                // 'sale_order_template_id' => 0,
                'validity_date' => now()->format('Y-m-d H:i:s'),
                'date_order' => now()->format('Y-m-d H:i:s'),
                // 'pricelist_id' => 1,
                // 'payment_term_id' => 1,
                // 'agent_id' => 86119,
                // 'pos_config_id' => 1,
                'user_id' => $client->search('res.users', [['name', '=', 'sky care kww']])->first(),
                'origin' => '#' . $order->increment_id,
            ];

            // $channel_code = $order->channel->code ?? 'default';
            // if ($channel_code == 'fitzone') {
            //     $odoo_data['agent_id'] = 86119;
            // }

            $odoo_order_id = $client->create('sale.order', $odoo_data);
            foreach ($order->items as $orderItem) {
                // if ($orderItem->sku == '100310') {
                //     $skus = [
                //         ['sku' => '128314', 'price' => 4.750],
                //         ['sku' => '128637', 'price' => 5.750]
                //     ];
                //     foreach ($skus as $sku) {
                //         $odoo_product_id = $client->search('product.product', [['default_code', '=', $sku['sku']]])->first();
                //         if ($odoo_product_id) {
                //             $odoo_line[] = [
                //                 'product_id' => $odoo_product_id,
                //                 'product_uom_qty' => $orderItem->qty_ordered,
                //                 'product_uom' => 1,
                //                 'price_unit' => $sku['price'],
                //                 'discount' => 0,
                //                 'order_id' => $odoo_order_id,
                //             ];
                //         }
                //     }
                // } else {
                $odoo_product_id = $client->search('product.product', [['default_code', '=', $orderItem->sku]])->first();
                if ($odoo_product_id) {
                    $odoo_line[] = [
                        'product_id' => $odoo_product_id,
                        'product_uom_qty' => $orderItem->qty_ordered,
                        'product_uom' => 1,
                        // 'price_unit' => $orderItem->base_price - ($orderItem->base_discount_amount / $orderItem->qty_ordered),
                        'price_unit' => $orderItem->base_price,
                        // 'discount' => number_format(((($orderItem->base_discount_amount / $orderItem->qty_ordered) / $orderItem->base_price) * 100), 2),
                        'discount' => 0,
                        // 'tax_id' => 1,
                        'order_id' => $odoo_order_id,
                    ];
                }
                // }
            }

            if ($order->base_discount_amount != 0) {
                $odoo_product_id = $client->search('product.product', [['name', '=', 'Extra Discount']])->first();
                if ($odoo_product_id) {
                    $odoo_line[] = [
                        'product_id' => $odoo_product_id,
                        'product_uom_qty' => 1,
                        'product_uom' => 1,
                        'price_unit' => -$order->base_discount_amount,
                        'discount' => 0,
                        // 'tax_id' => 1,
                        'order_id' => $odoo_order_id,
                    ];
                }
            }

            if ($order->shipping_method == 'internal_internal' && $order->shipping_amount != 0) {
                $rate = core()->getConfigData('sales.carriers.internal.default_rate');
                $odoo_product_id = $client->search('product.product', [['default_code', '=', "000000"]])->first();

                if ($odoo_product_id) {
                    $odoo_line[] = [
                        'product_id' => $odoo_product_id,
                        'product_uom_qty' => 1,
                        'product_uom' => 1,
                        'price_unit' => $rate,
                        'discount' => 0,
                        'order_id' => $odoo_order_id,
                    ];
                }
            } elseif ($order->shipping_method == 'flatrate_flatrate') {
                $rate = core()->getConfigData('sales.carriers.flatrate.default_rate');
                $shipping_qty = $order->shipping_amount / $rate;
                $odoo_product_id = $client->search('product.product', [['default_code', '=', 666666]])->first();
                if ($odoo_product_id) {
                    $odoo_line[] = [
                        'product_id' => $odoo_product_id,
                        'product_uom_qty' => $shipping_qty,
                        'product_uom' => 1,
                        'price_unit' => $rate,
                        'discount' => 0,
                        'order_id' => $odoo_order_id,
                    ];
                }
            }

            if (isset($odoo_line)) {

                if ($order->payment->method == 'cashondelivery') {
                    $name = "NOT PAID ";
                } else {
                    $name = "PAID Website ";
                }

                $name = $name . '#' . $order->increment_id;

                // $name = ($order->payment->method == 'cashondelivery' ? "NOT PAID " : ($order->payment->method == 'tabby' ? "TABBY PAID " : "PAID Website ")) . '#' . $order->increment_id;
                $odoo_line[] = [
                    'name' => $name,
                    "display_type" => "line_note",
                    'order_id' => $odoo_order_id,
                ];

                if ($address->note) {
                    $odoo_line[] = [
                        'name' => $address->note,
                        "display_type" => "line_note",
                        'order_id' => $odoo_order_id,
                    ];
                }

                $client->create('sale.order.line', $odoo_line);
                $client->write('sale.order', $odoo_order_id, ['amount_total' => $order->base_grand_total]);
            }
        }
    }
}
