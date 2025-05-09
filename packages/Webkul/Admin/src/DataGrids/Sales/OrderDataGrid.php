<?php

namespace Webkul\Admin\DataGrids\Sales;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderAddress;
use Webkul\Sales\Repositories\OrderRepository;

class OrderDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
            ->leftJoin('addresses as order_address_shipping', function ($leftJoin) {
                $leftJoin->on('order_address_shipping.order_id', '=', 'orders.id')
                    ->where('order_address_shipping.address_type', OrderAddress::ADDRESS_TYPE_SHIPPING);
            })
            ->leftJoin('addresses as order_address_billing', function ($leftJoin) {
                $leftJoin->on('order_address_billing.order_id', '=', 'orders.id')
                    ->where('order_address_billing.address_type', OrderAddress::ADDRESS_TYPE_BILLING);
            })
            ->leftJoin('order_payment', 'orders.id', '=', 'order_payment.order_id')
            ->select(
                'orders.id',
                'order_payment.method',
                'orders.increment_id',
                'orders.base_grand_total',
                'orders.created_at',
                // 'channel_name',
                DB::raw("COALESCE(customer_groups.code, 'general') as customer_group"),
                'orders.status',
                'customer_email',
                // 'orders.cart_id as image',
                DB::raw('CONCAT(' . DB::getTablePrefix() . 'orders.customer_first_name, " ", ' . DB::getTablePrefix() . 'orders.customer_last_name) as full_name'),
                DB::raw('CONCAT(' . DB::getTablePrefix() . 'order_address_billing.city, ", ", ' . DB::getTablePrefix() . 'order_address_billing.state,", ", ' . DB::getTablePrefix() . 'order_address_billing.country) as location'),
                DB::raw('CASE WHEN ' . DB::getTablePrefix() . 'order_address_shipping.phone_code IS NOT NULL THEN CONCAT(' . DB::getTablePrefix() . 'order_address_shipping.phone_code, " ", ' . DB::getTablePrefix() . 'order_address_shipping.phone) ELSE ' . DB::getTablePrefix() . 'order_address_shipping.phone END as phone')
            );

        if (request()->route()->getName() == 'admin.sales.abandoned-orders.index') {
            $queryBuilder->where('orders.status', 'no_status');
        } else {
            $queryBuilder->where('orders.status', '!=', 'no_status');
        }

        $queryBuilder->groupBy('orders.id');

        $this->addFilter('increment_id', 'orders.increment_id');
        $this->addFilter('full_name', DB::raw('CONCAT(' . DB::getTablePrefix() . 'orders.customer_first_name, " ", ' . DB::getTablePrefix() . 'orders.customer_last_name)'));
        $this->addFilter('phone', DB::raw('COALESCE(order_address_shipping.phone, order_address_billing.phone)'));
        $this->addFilter('customer_group', 'customer_groups.code');
        $this->addFilter('created_at', 'orders.created_at');
        $this->addFilter('status', 'orders.status');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'increment_id',
            'label'      => trans('admin::app.sales.orders.index.datagrid.order-id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('admin::app.sales.orders.index.datagrid.status'),
            'type'       => 'dropdown',
            'options'    => [
                'type' => 'basic',

                'params' => [
                    'options' => [
                        [
                            'label'  => trans('admin::app.sales.orders.index.datagrid.processing'),
                            'value'  => Order::STATUS_PROCESSING,
                        ],
                        [
                            'label'  => trans('admin::app.sales.orders.index.datagrid.completed'),
                            'value'  => Order::STATUS_COMPLETED,
                        ],
                        [
                            'label'  => trans('admin::app.sales.orders.index.datagrid.canceled'),
                            'value'  => Order::STATUS_CANCELED,
                        ],
                        [
                            'label'  => trans('admin::app.sales.orders.index.datagrid.closed'),
                            'value'  => Order::STATUS_CLOSED,
                        ],
                        [
                            'label'  => trans('admin::app.sales.orders.index.datagrid.pending'),
                            'value'  => Order::STATUS_PENDING,
                        ],
                        [
                            'label'  => trans('admin::app.sales.orders.index.datagrid.pending-payment'),
                            'value'  => Order::STATUS_PENDING_PAYMENT,
                        ],
                        [
                            'label'  => trans('admin::app.sales.orders.index.datagrid.fraud'),
                            'value'  => Order::STATUS_FRAUD,
                        ],
                    ],
                ],
            ],
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                switch ($row->status) {
                    case Order::STATUS_PROCESSING:
                        return '<p class="label-processing">' . trans('admin::app.sales.orders.index.datagrid.processing') . '</p>';

                    case Order::STATUS_COMPLETED:
                        return '<p class="label-active">' . trans('admin::app.sales.orders.index.datagrid.completed') . '</p>';

                    case Order::STATUS_CANCELED:
                        return '<p class="label-canceled">' . trans('admin::app.sales.orders.index.datagrid.canceled') . '</p>';

                    case Order::STATUS_CLOSED:
                        return '<p class="label-closed">' . trans('admin::app.sales.orders.index.datagrid.closed') . '</p>';

                    case Order::STATUS_PENDING:
                        return '<p class="label-pending">' . trans('admin::app.sales.orders.index.datagrid.pending') . '</p>';

                    case Order::STATUS_PENDING_PAYMENT:
                        return '<p class="label-pending">' . trans('admin::app.sales.orders.index.datagrid.pending-payment') . '</p>';

                    case Order::STATUS_FRAUD:
                        return '<p class="label-canceled">' . trans('admin::app.sales.orders.index.datagrid.fraud') . '</p>';
                }
            },
        ]);

        $this->addColumn([
            'index'      => 'base_grand_total',
            'label'      => trans('admin::app.sales.orders.index.datagrid.grand-total'),
            'type'       => 'price',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return core()->convertToBasePrice($row->base_grand_total);
            },
        ]);

        $this->addColumn([
            'index'      => 'method',
            'label'      => trans('admin::app.sales.orders.index.datagrid.pay-via'),
            'type'       => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => false,
            'closure'    => function ($row) {
                return core()->getConfigData('sales.payment_methods.' . $row->method . '.title');
            },
        ]);

        $this->addColumn([
            'index'      => 'customer_group',
            'label'      => trans('admin::app.sales.orders.view.customer-group'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('admin::app.sales.orders.index.datagrid.customer'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        /**
         * Searchable dropdown sample. In testing phase.
         */
        $this->addColumn([
            'index'      => 'customer_email',
            'label'      => trans('admin::app.sales.orders.index.datagrid.email'),
            'type'       => 'dropdown',
            'options'    => [
                'type'   => 'searchable',
                'params' => [
                    'repository' => \Webkul\Customer\Repositories\CustomerRepository::class,
                    'column'     => [
                        'label' => 'email',
                        'value' => 'email',
                    ],
                ],
            ],
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'location',
            'label'      => trans('admin::app.sales.orders.index.datagrid.location'),
            'type'       => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => false,
        ]);

        // $this->addColumn([
        //     'index'      => 'image',
        //     'label'      => trans('admin::app.sales.orders.index.datagrid.images'),
        //     'type'       => 'string',
        //     'searchable' => false,
        //     'filterable' => false,
        //     'sortable'   => false,
        //     'closure'    => function ($value) {
        //         $order = app(OrderRepository::class)->with('items')->find($value->id);

        //         return view('admin::sales.orders.images', compact('order'))->render();
        //     },
        // ]);


        $this->addColumn([
            'index'      => 'phone',
            'label'      => trans('admin::app.sales.orders.index.datagrid.phone'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);


        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('admin::app.sales.orders.index.datagrid.date'),
            'type'       => 'date_range',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('sales.orders.view')) {
            $this->addAction([
                'icon'   => 'icon-view',
                'title'  => trans('admin::app.sales.orders.index.datagrid.view'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.sales.orders.view', $row->id);
                },
            ]);
        }

        $this->addAction([
            'icon'   => 'icon-export',
            'title'  => trans('admin::app.sales.orders.index.datagrid.download-pdf'),
            'method' => 'GET',
            'url'    => function ($row) {
                $invoice = Invoice::where('order_id', $row->id)->first();
                if ($invoice) {
                    return route('admin.sales.invoices.print', $invoice->id ?? 0);
                }
                return "#";
            },
            'onclick' => 'openSmallWindow(event)',
        ]);
    }
}
