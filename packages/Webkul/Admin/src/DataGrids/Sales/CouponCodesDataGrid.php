<?php

namespace Webkul\Admin\DataGrids\Sales;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class CouponCodesDataGrid extends DataGrid
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

        // if (isset($data['filters']['created_at'])) {
        //     unset($data['filters']['created_at']);
        //     request()->merge($data);
        // }

        $queryBuilder = DB::table('cart_rules')
            // ->leftJoin('cart_rule_coupons', function ($leftJoin) {
            //     $leftJoin->on('cart_rule_coupons.cart_rule_id', '=', 'cart_rules.id')
            //         ->where('cart_rule_coupons.is_primary', 1);
            // })
            ->leftJoin('orders', function ($leftJoin) use ($start_date, $end_date) {
                $leftJoin->whereNotIn('orders.status', ['no_status', 'canceled', 'closed', 'fraud'])
                    ->whereBetween('orders.created_at', [$start_date, $end_date])
                    ->whereRaw("FIND_IN_SET(cart_rules.id, orders.applied_cart_rule_ids)");
            });

        $queryBuilder->addSelect(
            'cart_rules.id',
            'name',
            DB::raw('ROUND(SUM(orders.base_sub_cost), 3) as total_base_sub_cost'),
            DB::raw('ROUND(SUM(orders.base_discount_amount), 3) as total_base_discount_amount'),
            DB::raw('ROUND((SUM(orders.base_sub_total) - SUM(orders.base_discount_amount)), 3) as total_base_sub_total'),
            DB::raw('ROUND((SUM(orders.base_sub_total) - SUM(orders.base_discount_amount) - SUM(orders.base_sub_cost)), 3) as profit'),
            DB::raw('COUNT(orders.id) as orders_count'),
            'orders.created_at'
        );

        $queryBuilder->groupBy('cart_rules.id');

        $this->addFilter('id', 'cart_rules.id');
        $this->addFilter('total_base_sub_cost', DB::raw('SUM(orders.base_sub_cost) as total_base_sub_cost'));
        $this->addFilter('total_base_discount_amount', DB::raw('SUM(orders.base_discount_amount) as total_base_discount_amount'));
        $this->addFilter('total_base_sub_total', DB::raw('ROUND((SUM(orders.base_sub_total) - SUM(orders.base_discount_amount)), 3) as total_base_sub_total'));
        $this->addFilter('orders_count', DB::raw('COUNT(orders.id) as orders_count'));
        $this->addFilter('created_at', 'orders.created_at');

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
            'index'      => 'orders_count',
            'label'      => trans('admin::app.reporting.sales.index.count'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'total_base_sub_cost',
            'label'      => "Cost",
            'type'       => 'price',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'total_base_discount_amount',
            'label'      => "Discount",
            'type'       => 'price',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'total_base_sub_total',
            'label'      => "Total",
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

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'icon'   => 'icon-edit',
            'title'  => trans('admin::app.catalog.products.index.datagrid.edit'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.marketing.promotions.cart_rules.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-view',
            'title'  => trans('admin::app.sales.orders.index.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.reporting.coupon_codes_report.view', $row->id);
            },
        ]);
    }
}
