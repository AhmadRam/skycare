<?php

namespace Webkul\Admin\DataGrids\Catalog;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\DataGrid\DataGrid;

class BrandDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('attribute_options')
            ->leftJoin('attribute_option_translations as attribute_options_en', function ($leftJoin) {
                $leftJoin->on('attribute_options.id', '=', 'attribute_options_en.attribute_option_id')
                    ->where('attribute_options_en.locale', 'en');
            })
            ->leftJoin('attribute_option_translations as attribute_options_ar', function ($leftJoin) {
                $leftJoin->on('attribute_options.id', '=', 'attribute_options_ar.attribute_option_id')
                    ->where('attribute_options_ar.locale', 'ar');
            })

            ->where('attribute_id', 25)
            ->addSelect(
                'attribute_options.id as id',
                'attribute_options_en.label as name_en',
                'attribute_options_ar.label as name_ar',
                'attribute_options.swatch_value as image',
                'attribute_options.sort_order',
                'attribute_options.status',
            )->groupBy('attribute_options.id');

        $this->addFilter('id', 'attribute_options.id');
        $this->addFilter('name_en', 'attribute_options_en.label');
        $this->addFilter('name_ar', 'attribute_options_ar.label');
        $this->addFilter('image', 'attribute_options.swatch_value');
        $this->addFilter('sort_order', 'attribute_options.sort_order');
        $this->addFilter('status', 'attribute_options.status');

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
            'index'      => 'id',
            'label'      => trans('admin::app.catalog.categories.index.datagrid.id'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);


        $this->addColumn([
            'index'      => 'name_en',
            'label'      => trans('admin::app.catalog.brands.name_en'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name_ar',
            'label'      => trans('admin::app.catalog.brands.name_ar'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);


        $this->addColumn([
            'index'      => 'image',
            'label'      => trans('admin::app.catalog.products.index.datagrid.image'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
            'closure'    => function ($row) {
                $image = Storage::url($row->image);
                return "<div class='d-flex'>" .
                    "<picture style='margin-inline-end: 10px;'><source srcset='{$image}' type='image/webp'><img src='{$image}' width='50px' height='50px'></picture>" .
                    "</div>";
            },
        ]);

        $this->addColumn([
            'index'      => 'sort_order',
            'label'      => trans('admin::app.catalog.brands.sort_order'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('admin::app.sales.orders.view.status'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
            'closure'    => function ($value) {
                $html = '';

                if ($value->status) {
                    $html .= '<span class="badge badge-md badge-success">' . trans('admin::app.settings.users.index.datagrid.active') . '</span>';
                } else {
                    $html .= '<span class="badge badge-md badge-danger">' . trans('admin::app.settings.users.index.datagrid.inactive') . '</span>';
                }

                return $html;
            },
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
                return route('admin.catalog.brands.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-trash',
            'title'  => trans('admin::app.catalog.products.index.datagrid.delete'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.catalog.brands.delete', $row->id);
            },
        ]);

    }
}
