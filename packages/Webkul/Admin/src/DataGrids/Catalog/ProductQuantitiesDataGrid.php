<?php

namespace Webkul\Admin\DataGrids\Catalog;

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\Locale;
use Webkul\DataGrid\DataGrid;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Product\Repositories\ProductRepository;

class ProductQuantitiesDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'product_id';

    /**
     * Constructor for the class.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        if (core()->getRequestedChannelCode() === 'all') {
            $whereInChannels = Channel::query()->pluck('code')->toArray();
        } else {
            $whereInChannels = [core()->getRequestedChannelCode()];
        }

        if (core()->getRequestedLocaleCode() === 'all') {
            $whereInLocales = Locale::query()->pluck('code')->toArray();
        } else {
            $whereInLocales = [core()->getRequestedLocaleCode()];
        }

        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('product_flat')
            ->leftJoin('product_inventories', 'product_flat.product_id', '=', 'product_inventories.product_id')
            ->select(
                'product_flat.product_id',
                'product_flat.sku',
                'product_flat.name',
                DB::raw('SUM(CASE WHEN product_inventories.inventory_source_id = 1 THEN product_inventories.qty ELSE 0 END) as inventory_1_qty'),
                DB::raw('SUM(CASE WHEN product_inventories.inventory_source_id = 2 THEN product_inventories.qty ELSE 0 END) as inventory_2_qty')
            )
            ->whereIn('product_flat.locale', $whereInLocales)
            ->whereIn('product_flat.channel', $whereInChannels)
            ->groupBy(
                'product_flat.product_id',
                'product_flat.sku',
                'product_flat.name'
            );

        $this->addFilter('product_id', 'product_flat.product_id');
        $this->addFilter('sku', 'product_flat.sku');
        $this->addFilter('name', 'product_flat.name');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'product_id',
            'label'      => trans('admin::app.catalog.products.index.datagrid.id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'sku',
            'label'      => trans('admin::app.catalog.products.index.datagrid.sku'),
            'type'       => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.catalog.products.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'inventory_1_qty',
            'label'      => "Default",
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'inventory_2_qty',
            'label'      => "MAIN STORE KABID",
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions() {}

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions() {}
}
