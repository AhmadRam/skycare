<?php

namespace Webkul\Admin\DataGrids\Sales;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class ProductSalesDataGrid extends DataGrid
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

        $queryBuilder = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->join('product_flat', 'order_items.product_id', '=', 'product_flat.product_id')->where('product_flat.locale', 'en')
            ->join('product_attribute_values as pav', function ($join) {
                $join->on('order_items.product_id', '=', 'pav.product_id')
                    ->where('pav.attribute_id', '=', 12);
            })
            ->addSelect(
                'customers.customer_group_id',
                'order_items.product_id as id',
                'product_flat.sku as sku',
                'product_flat.name as name',
                DB::raw('SUM(base_total_invoiced - base_amount_refunded) as revenue'),
                DB::raw('SUM(qty_invoiced - qty_refunded) as total_qty'),
                DB::raw('SUM(pav.float_value * (qty_invoiced - qty_refunded)) as total_cost'),
                DB::raw('SUM((base_total_invoiced - base_amount_refunded) - (pav.float_value * (qty_invoiced - qty_refunded))) as profit')
            )
            ->whereNull('order_items.parent_id')
            ->whereBetween('order_items.created_at', [$start_date, $end_date]);


        if (isset($data['customer_group'])) {
            $queryBuilder->where('customers.customer_group_id', $data['customer_group']);
        }

        $queryBuilder->having(DB::raw('SUM(base_total_invoiced - base_amount_refunded)'), '>', 0)
            ->groupBy('order_items.product_id');

        $this->addFilter('id', 'product_flat.product_id');
        $this->addFilter('sku', 'product_flat.sku');
        $this->addFilter('name', 'product_flat.name');
        $this->addFilter('total_qty', DB::raw('SUM(qty_invoiced - qty_refunded)'));
        $this->addFilter('revenue', DB::raw('SUM(base_total_invoiced - base_amount_refunded)'));
        $this->addFilter('total_cost', DB::raw('SUM((base_total_invoiced - base_amount_refunded) - (pav.float_value * (qty_invoiced - qty_refunded)))'));

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
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'total_qty',
            'label'      => trans('admin::app.reporting.products.sales.total_qty'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'revenue',
            'label'      => trans('admin::app.reporting.products.index.revenue'),
            'type'       => 'price',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'total_cost',
            'label'      => trans('admin::app.reporting.products.sales.total_cost'),
            'type'       => 'price',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'profit',
            'label'      => trans('admin::app.reporting.products.sales.profit'),
            'type'       => 'price',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);
    }
}
