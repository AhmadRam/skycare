<?php

namespace Webkul\Admin\DataGrids\Sales;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class BrandProductSalesDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $data = request()->all();
        $start_date = $data['filters']['created_at'][0] ?? '1999-01-01';
        $end_date = $data['filters']['created_at'][1] ?? '2999-01-01';


        $attributeId = 25;
        $attributeValueId = $data['brand_id'] ?? 0;
        
        $customer_group =  $data['customer_group_id'] ?? 0;

        $queryBuilder = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            // ->join('product_flat', 'order_items.product_id', '=', 'product_flat.product_id')
            ->join('product_attribute_values', 'order_items.product_id', '=', 'product_attribute_values.product_id')
            ->join('attribute_options', 'product_attribute_values.integer_value', '=', 'attribute_options.id');

            if ($customer_group != null && $customer_group != 0) {
                $group_condition = ['=', $customer_group];
                $queryBuilder = $queryBuilder->join('customers', 'orders.customer_id', '=', 'customers.id')
                    ->join('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
                    ->where(function ($query) use ($group_condition) {
                        $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1]);
                        if ($group_condition[1] != "3") {
                            $query->orWhereNull('orders.customer_id');
                        }
                    });
            }
    
            $queryBuilder = $queryBuilder->where('product_attribute_values.attribute_id', $attributeId)
            ->where('orders.status', 'completed')
            ->where('product_attribute_values.integer_value', $attributeValueId)
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->groupBy('order_items.id')
            ->select(
                'order_items.id',
                'orders.increment_id',
                'order_items.sku',
                'order_items.name',
                'order_items.coupon_code',
                'order_items.qty_ordered',
                'order_items.qty_shipped',
                'order_items.qty_canceled',
                'order_items.qty_refunded',
                'order_items.base_price',
                'order_items.base_total',
                'order_items.discount_percent',
                'order_items.discount_amount',
                'order_items.created_at',
            );

        $this->addFilter('sku', 'order_items.sku');
        $this->addFilter('order_id', 'orders.increment_id');
        $this->addFilter('name', 'order_items.name');
        $this->addFilter('coupon_code', 'order_items.coupon_code');
        $this->addFilter('qty_ordered', 'order_items.qty_ordered');
        $this->addFilter('qty_shipped', 'order_items.qty_shipped');
        $this->addFilter('qty_canceled', 'order_items.qty_canceled');
        $this->addFilter('qty_refunded', 'order_items.qty_refunded');
        $this->addFilter('base_price', 'order_items.base_price');
        $this->addFilter('base_total', 'order_items.base_total');
        $this->addFilter('discount_percent', 'order_items.discount_percent');
        $this->addFilter('discount_amount', 'order_items.discount_amount');
        $this->addFilter('created_at', 'order_items.created_at');

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
            'index'      => 'id',
            'label'      => trans('admin::app.reporting.sales.index.id'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'increment_id',
            'label'      => "Order Id",
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'sku',
            'label'      => trans('admin::app.emails.orders.sku'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.emails.orders.name'),
            'type'       => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'coupon_code',
            'label'      => trans('admin::app.marketing.promotions.cart-rules.edit.coupon-code'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'qty_ordered',
            'label'      => trans('admin::app.sales.invoices.invoice-pdf.qty'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'qty_shipped',
            'label'      => trans('admin::app.sales.shipments.create.qty-to-ship'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'qty_canceled',
            'label'      => "Qty. to canceled",
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'qty_refunded',
            'label'      => trans('admin::app.sales.refunds.create.qty-to-refund'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'base_price',
            'label'      => trans('admin::app.emails.orders.price'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'base_total',
            'label'      => trans('admin::app.reporting.sales.index.total'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'discount_percent',
            'label'      => "Discount %",
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'discount_amount',
            'label'      => trans('admin::app.sales.refunds.create.discount-amount'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('admin::app.marketing.promotions.cart-rules-coupons.datagrid.created-date'),
            'type'       => 'date',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions() {}
}
