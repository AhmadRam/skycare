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
            ->join('orders', 'order_items.order_id', '=', 'orders.id');

        $customer_group =  $data['customer_group'] ?? 0;
        if ($customer_group != null && $customer_group != 0) {
            $customer_group = (int) $customer_group;
            $group_condition = $customer_group != 3 ? ['!=', 3] : ['=', $customer_group];
            $queryBuilder = $queryBuilder->leftjoin('customers', 'orders.customer_id', '=', 'customers.id')
                ->where(function ($query) use ($group_condition) {
                    $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1]);
                    if ($group_condition[0] == "!=") {
                        $query->orWhereNull('orders.customer_id');
                    }
                });
        }

        $queryBuilder = $queryBuilder->addSelect(
            'order_items.product_id as id',
            'order_items.sku as sku',
            'order_items.name as name',
            DB::raw('SUM(base_total_invoiced - base_amount_refunded) as revenue'),
            DB::raw('SUM(qty_invoiced - qty_refunded) as total_qty'),
            DB::raw('SUM(order_items.base_cost * (qty_invoiced - qty_refunded)) as total_cost'),
            "order_items.base_discount_amount",
            DB::raw('SUM((base_total_invoiced - base_amount_refunded) - (order_items.base_cost * (qty_invoiced - qty_refunded))) as profit')
        )
            ->whereNull('order_items.parent_id')
            ->where('orders.status', 'completed')
            ->whereBetween('order_items.created_at', [$start_date, $end_date]);

        $queryBuilder->having(DB::raw('SUM(base_total_invoiced - base_amount_refunded)'), '>', 0)
            ->groupBy('order_items.product_id');

        $this->addFilter('id', 'order_items.product_id');
        $this->addFilter('sku', 'order_items.sku');
        $this->addFilter('name', 'order_items.name');
        $this->addFilter('revenue', DB::raw('SUM(base_total_invoiced - base_amount_refunded)'));
        $this->addFilter('total_qty', DB::raw('SUM(qty_invoiced - qty_refunded)'));
        $this->addFilter('total_cost', DB::raw('SUM(order_items.base_cost * (qty_invoiced - qty_refunded))'));
        $this->addFilter('base_discount_amount', 'order_items.base_discount_amount');
        $this->addFilter('profit', DB::raw('SUM((base_total_invoiced - base_amount_refunded) - (order_items.base_cost * (qty_invoiced - qty_refunded)))'));

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
            'index'      => 'base_discount_amount',
            'label'      => "Discount",
            'type'       => 'price',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return core()->convertToBasePrice($row->base_discount_amount);
            },
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
