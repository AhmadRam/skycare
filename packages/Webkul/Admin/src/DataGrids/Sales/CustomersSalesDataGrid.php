<?php

namespace Webkul\Admin\DataGrids\Sales;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class CustomersSalesDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $data = request()->all();
        $start_date = $data['filters']['created_at'][0] ?? '1999-01-01';
        $end_date = $data['filters']['created_at'][1] ?? '2999-01-01';

        $customer_group =  $data['customer_group_id'] ?? 0;

        $queryBuilder = DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->leftJoin('addresses', 'addresses.order_id', '=', 'orders.id');

        // تصفية مجموعة العملاء إذا لزم الأمر
        if ($customer_group != null && $customer_group != 0) {
            $customer_group = (int) $customer_group;
            $group_condition = $customer_group != 3 ? ['!=', 3] : ['=', $customer_group];

            $queryBuilder = $queryBuilder->where(function ($query) use ($group_condition) {
                $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1])
                    ->orWhereNull('orders.customer_id');
            });
        }

        $queryBuilder = $queryBuilder
            ->whereNotIn('orders.status', ['no_status', 'canceled', 'closed', 'fraud'])
            ->whereBetween('orders.created_at', [$start_date, $end_date])
            ->where('addresses.address_type', 'order_shipping')
            ->select(
                DB::raw('COALESCE(customers.id, 0) as id'),
                // الاسم الكامل حسب الأولوية: من العنوان > من الطلب > من العميل
                DB::raw('CASE
                    WHEN addresses.first_name IS NOT NULL THEN CONCAT(' . $tablePrefix . 'addresses.first_name, " ", ' . $tablePrefix . 'addresses.last_name)
                    WHEN orders.customer_id IS NULL THEN CONCAT(' . $tablePrefix . 'orders.customer_first_name, " ", ' . $tablePrefix . 'orders.customer_last_name)
                    ELSE CONCAT(' . $tablePrefix . 'customers.first_name, " ", ' . $tablePrefix . 'customers.last_name)
                END as full_name'),
                'orders.is_guest',
                DB::raw('COALESCE(addresses.email, orders.customer_email, customers.email) as email'),
                DB::raw('COALESCE(addresses.phone, customers.phone) as phone'),
                // البيانات الحسابية
                DB::raw('ROUND(SUM(orders.base_sub_cost), 3) as total_base_sub_cost'),
                DB::raw('ROUND(SUM(orders.base_discount_amount), 3) as total_base_discount_amount'),
                DB::raw('ROUND((SUM(orders.base_sub_total) - SUM(orders.base_discount_amount)), 3) as total_base_sub_total'),
                DB::raw('ROUND((SUM(orders.base_sub_total) - SUM(orders.base_discount_amount) - SUM(orders.base_sub_cost)), 3) as profit'),
                DB::raw('COUNT(orders.id) as orders_count'),
            )
            ->groupBy(
                DB::raw('COALESCE(customers.id, 0)'),
                DB::raw('COALESCE(orders.customer_email, customers.email)'),
                'orders.is_guest',
                'addresses.first_name', // مازلنا نحتاجها للتجميع
                'addresses.last_name'   // مازلنا نحتاجها للتجميع
            );

        $this->addFilter('id', 'customers.id');
        $this->addFilter('full_name', DB::raw('CASE WHEN addresses.first_name IS NOT NULL THEN CONCAT(' . $tablePrefix . 'addresses.first_name, " ", ' . $tablePrefix . 'addresses.last_name) WHEN orders.customer_id IS NULL THEN CONCAT(' . $tablePrefix . 'orders.customer_first_name, " ", ' . $tablePrefix . 'orders.customer_last_name) ELSE CONCAT(' . $tablePrefix . 'customers.first_name, " ", ' . $tablePrefix . 'customers.last_name) END'));
        $this->addFilter('phone', DB::raw('COALESCE(addresses.phone, customers.phone)'));
        $this->addFilter('email', DB::raw('COALESCE(addresses.email, orders.customer_email, customers.email)'));
        $this->addFilter('total_base_sub_cost', DB::raw('SUM(orders.base_sub_cost) as total_base_sub_cost'));
        $this->addFilter('total_base_discount_amount', DB::raw('SUM(orders.base_discount_amount) as total_base_discount_amount'));
        $this->addFilter('total_base_sub_total', DB::raw('ROUND((SUM(orders.base_sub_total) - SUM(orders.base_discount_amount)), 3) as total_base_sub_total'));
        $this->addFilter('profit', DB::raw('ROUND((SUM(orders.base_sub_total) - SUM(orders.base_discount_amount) - SUM(orders.base_sub_cost)), 3) as profit'));
        $this->addFilter('orders_count', DB::raw('COUNT(orders.id) as orders_count'));

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
            'label'      => trans('admin::app.customers.customers.index.datagrid.id'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('admin::app.customers.customers.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => trans('admin::app.customers.customers.index.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'phone',
            'label'      => trans('admin::app.customers.customers.index.datagrid.phone'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => false,
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
    public function prepareActions() {}
}
