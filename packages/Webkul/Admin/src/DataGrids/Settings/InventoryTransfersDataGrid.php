<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Support\Facades\DB;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\Locale;
use Webkul\DataGrid\DataGrid;

class InventoryTransfersDataGrid extends DataGrid
{
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

        $queryBuilder = DB::table('inventory_transfers')
            ->leftJoin('product_flat', 'inventory_transfers.product_id', '=', 'product_flat.product_id')
            ->leftJoin('inventory_sources as from', 'inventory_transfers.from_inventory_id', '=', 'from.id')
            ->leftJoin('inventory_sources as to', 'inventory_transfers.to_inventory_id', '=', 'to.id')
            ->whereIn('product_flat.locale', $whereInLocales)
            ->whereIn('product_flat.channel', $whereInChannels)
            ->addSelect('inventory_transfers.id as id', 'product_flat.name as product_name', 'from.name as from_name', 'to.name as to_name', 'inventory_transfers.quantity as quantity');


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
            'label'      => trans('admin::app.settings.inventory-sources.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'product_name',
            'label'      => trans('admin::app.account.edit.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'from_name',
            'label'      => trans('admin::app.settings.inventory-sources.transfer.from'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'to_name',
            'label'      => trans('admin::app.settings.inventory-sources.transfer.to'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'quantity',
            'label'      => trans('admin::app.settings.inventory-sources.transfer.qty'),
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions() {}
}
