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
        // $start_date = request()->startDate ?? '1999-01-01';
        // $end_date = request()->endDate != '?v=1' ? request()->endDate : '2999-01-01';
        // $end_date = str_replace('?v=1', ' ', $end_date);

        $queryBuilder = DB::table('cart_rules')
            ->leftJoin('cart_rule_coupons', function ($leftJoin) {
                $leftJoin->on('cart_rule_coupons.cart_rule_id', '=', 'cart_rules.id')
                    ->where('cart_rule_coupons.is_primary', 1);
            })
            ->leftJoin('orders', 'cart_rules.id', '=', 'orders.applied_cart_rule_ids');


        $queryBuilder->addSelect(
            'cart_rules.id',
            'name',
            'cart_rule_coupons.code as coupon_code',
            'cart_rule_coupons.times_used',
            DB::raw('SUM(orders.base_discount_amount) as total_base_discount_amount'),
            DB::raw('COUNT(orders.id) as orders_count'),
            'orders.created_at as created_at'
        );

        $queryBuilder->groupBy('cart_rules.id');

        $this->addFilter('id', 'cart_rules.id');
        $this->addFilter('coupon_code', 'cart_rule_coupons.code');
        $this->addFilter('times_used', 'cart_rule_coupons.times_used');
        $this->addFilter('total_base_discount_amount', DB::raw('SUM(orders.base_discount_amount) as total_base_discount_amount'));
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
            'index'      => 'coupon_code',
            'label'      => trans('admin::app.marketing.promotions.cart-rules.edit.coupon-code'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'times_used',
            'label'      => trans('admin::app.marketing.promotions.cart-rules-coupons.datagrid.times-used'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'total_base_discount_amount',
            'label'      => trans('admin::app.emails.orders.grand-total'),
            'type'       => 'price',
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
        $this->addAction([
            'icon'   => 'icon-edit',
            'title'  => trans('admin::app.catalog.products.index.datagrid.edit'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.marketing.promotions.cart_rules.edit', $row->id);
            },
        ]);
    }
}
