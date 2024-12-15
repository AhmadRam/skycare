<?php

namespace Webkul\Admin\Http\Controllers\Catalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Admin\DataGrids\BrandProductDataGrid;
use Webkul\Admin\DataGrids\Catalog\BrandDataGrid;
use Webkul\Attribute\Models\AttributeOption;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Admin\Http\Controllers\Controller;

class BrandController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Attribute\Repositories\AttributeRepository  $attributeRepository
     * @param  \Webkul\Product\Repositories\ProductRepository  $productRepository
     * @param  \Webkul\Product\Repositories\AttributeOptionRepository  $attributeOptionRepository
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected ProductRepository $productRepository,
        protected AttributeOptionRepository $attributeOptionRepository,
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(BrandDataGrid::class)->toJson();
        }

        return view('admin::catalog.brands.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::catalog.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'label.*' => 'required',
        ]);

        $params = request()->all();
        $params['attribute_id'] = 25;
        // $params['channels'] = implode(',', $params['channels']);

        if (
            isset($params['locale'])
            && $params['locale'] == 'all'
        ) {
            $model = app()->make(AttributeOption::class);

            foreach (core()->getAllLocales() as $locale) {
                foreach ($model->translatedAttributes as $attribute) {
                    if (isset($params[$attribute])) {
                        $params[$locale->code][$attribute] = $params[$attribute];

                        $params[$locale->code]['locale'] = $locale->code;
                    }
                }
            }
        }

        $params["swatch_value"] = $params["swatch_value"]['image_0'] ?? null;
        if ($params["swatch_value"] == null) {
            unset($params["swatch_value"]);
        }

        Event::dispatch('catalog.attribute.create.before');

        $attribute = $this->attributeOptionRepository->create($params);

        Event::dispatch('catalog.attribute.create.after', $attribute);

        session()->flash('success', trans('admin::app.catalog.attributes.create-success'));

        return redirect()->route('admin.catalog.brands.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $brand = $this->attributeOptionRepository->findOrFail($id);

        return view('admin::catalog.brands.edit', compact('brand'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $this->validate(request(), [
            'label.*' => 'required',
        ]);

        $params = request()->all();
        $params["swatch_value"] = $params["swatch_value"]['image_0'] ?? null;
        // $params['channels'] = implode(',', $params['channels']);

        if ($params["swatch_value"] == null) {
            unset($params["swatch_value"]);
        }
        Event::dispatch('catalog.attribute.update.before', $id);

        $brand = $this->attributeOptionRepository->update($params, $id);

        Event::dispatch('catalog.attribute.update.after', $brand);

        session()->flash('success', trans('admin::app.catalog.attributes.update-success'));

        return redirect()->route('admin.catalog.brands.index');
    }

    // /**
    //  * Show the products of specified resource.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\View\View
    //  */
    // public function products($id)
    // {
    //     if (request()->ajax()) {
    //         return app(BrandProductDataGrid::class)->toJson();
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Event::dispatch('catalog.attribute.delete.before', $id);

            $this->attributeOptionRepository->delete($id);

            Event::dispatch('catalog.attribute.delete.after', $id);

            return response()->json(['message' => trans('admin::app.catalog.attributes.delete-success')]);
        } catch (\Exception $e) {
            return response()->json(['message' => trans('admin::app.catalog.attributes.delete-failed')], 500);
        }
    }

    /**
     * Remove the specified resources from database.
     *
     * @return \Illuminate\Http\Response
     */
    public function massDestroy()
    {
        if (request()->isMethod('post')) {
            $indexes = explode(',', request()->input('indexes'));

            foreach ($indexes as $index) {
                Event::dispatch('catalog.attribute.delete.before', $index);

                $this->attributeOptionRepository->delete($index);

                Event::dispatch('catalog.attribute.delete.after', $index);
            }

            session()->flash('success', trans('admin::app.datagrid.mass-ops.delete-success', ['resource' => 'attributes']));
        } else {
            session()->flash('error', trans('admin::app.datagrid.mass-ops.method-error'));
        }

        return redirect()->back();
    }
}
