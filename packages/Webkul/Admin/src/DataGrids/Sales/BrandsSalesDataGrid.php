<?php

namespace Webkul\Admin\DataGrids\Sales;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class BrandsSalesDataGrid extends DataGrid
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

        $queryBuilder = DB::table('attribute_options')
            ->join('product_attribute_values', 'attribute_options.id', '=', 'product_attribute_values.integer_value')
            ->join('order_items', 'product_attribute_values.product_id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('attribute_options.attribute_id', $attributeId)
            ->whereBetween('order_items.created_at', [$start_date, $end_date])
            // ->where('product_attribute_values.integer_value', 15)
            ->groupBy('attribute_options.id')
            ->select(
                'product_attribute_values.integer_value as id',
                'attribute_options.admin_name as name',
                DB::raw('SUM(order_items.base_total) as base_total'),
                DB::raw('SUM(order_items.qty_ordered) as total_quantity')
            );

        $this->addFilter('id', 'product_attribute_values.integer_value');
        $this->addFilter('name', 'attribute_options.admin_name');
        $this->addFilter('base_total', DB::raw('SUM(order_items.base_total)'));
        $this->addFilter('total_quantity', DB::raw('SUM(order_items.qty_ordered)'));

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
            'index'      => 'name',
            'label'      => trans('admin::app.reporting.sales.index.name'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'base_total',
            'label'      => trans('admin::app.emails.orders.grand-total'),
            'type'       => 'price',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return core()->convertToBasePrice($row->base_total);
            },
        ]);

        $this->addColumn([
            'index'      => 'total_quantity',
            'label'      => trans('admin::app.reporting.products.index.quantities'),
            'type'       => 'number',
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
    public function prepareActions()
    {

        $this->addAction([
            'icon'   => 'icon-view',
            'title'  => trans('admin::app.sales.orders.index.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.reporting.brands_sales_report.view', $row->id);
            },
        ]);
    }
}
