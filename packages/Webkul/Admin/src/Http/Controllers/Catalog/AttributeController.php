<?php

namespace Webkul\Admin\Http\Controllers\Catalog;

use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\DataGrids\Catalog\AttributeDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Core\Rules\Code;
use Webkul\Product\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Webkul\Attribute\Repositories\AttributeOptionRepository;

class AttributeController extends Controller
{
    /**
     * Create a new controller instance.
     *
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
            return app(AttributeDataGrid::class)->toJson();
        }

        return view('admin::catalog.attributes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::catalog.attributes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'code'          => ['required', 'not_in:type,attribute_family_id', 'unique:attributes,code', new Code()],
            'admin_name'    => 'required',
            'type'          => 'required',
            'default_value' => 'integer',
        ]);

        $requestData = request()->all();

        $requestData['default_value'] ??= null;

        Event::dispatch('catalog.attribute.create.before');

        $attribute = $this->attributeRepository->create($requestData);

        Event::dispatch('catalog.attribute.create.after', $attribute);

        session()->flash('success', trans('admin::app.catalog.attributes.create-success'));

        return redirect()->route('admin.catalog.attributes.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $attribute = $this->attributeRepository->findOrFail($id);

        return view('admin::catalog.attributes.edit', compact('attribute'));
    }

    /**
     * Get attribute options associated with attribute.
     *
     * @return \Illuminate\View\View
     */
    public function getAttributeOptions(int $id)
    {
        $attribute = $this->attributeRepository->findOrFail($id);

        return $attribute->options()->orderBy('sort_order')->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(int $id)
    {
        $this->validate(request(), [
            'code'          => ['required', 'unique:attributes,code,' . $id, new Code],
            'admin_name'    => 'required',
            'type'          => 'required',
            'default_value' => 'integer',
        ]);

        $requestData = request()->all();

        if (!$requestData['default_value']) {
            $requestData['default_value'] = null;
        }

        Event::dispatch('catalog.attribute.update.before', $id);

        $attribute = $this->attributeRepository->update($requestData, $id);

        Event::dispatch('catalog.attribute.update.after', $attribute);

        session()->flash('success', trans('admin::app.catalog.attributes.update-success'));

        return redirect()->route('admin.catalog.attributes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $attribute = $this->attributeRepository->findOrFail($id);

        if (!$attribute->is_user_defined) {
            return response()->json([
                'message' => trans('admin::app.catalog.attributes.user-define-error'),
            ], 400);
        }

        try {
            Event::dispatch('catalog.attribute.delete.before', $id);

            $this->attributeRepository->delete($id);

            Event::dispatch('catalog.attribute.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.catalog.attributes.delete-success'),
            ]);
        } catch (\Exception $e) {
        }

        return new JsonResponse([
            'message' => trans('admin::app.catalog.attributes.delete-failed'),
        ], 500);
    }

    /**
     * Remove the specified resources from database.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            $attribute = $this->attributeRepository->find($index);

            if (!$attribute->is_user_defined) {
                return response()->json([
                    'message' => trans('admin::app.catalog.attributes.delete-failed'),
                ], 422);
            }
        }

        foreach ($indices as $index) {
            Event::dispatch('catalog.attribute.delete.before', $index);

            $this->attributeRepository->delete($index);

            Event::dispatch('catalog.attribute.delete.after', $index);
        }

        return new JsonResponse([
            'message' => trans('admin::app.catalog.attributes.index.datagrid.mass-delete-success'),
        ]);
    }

    /**
     * Get super attributes of product.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function productSuperAttributes(int $id)
    {
        $product = $this->productRepository->findOrFail($id);

        $superAttributes = $this->productRepository->getSuperAttributes($product);

        return response()->json([
            'data'  => $superAttributes,
        ]);
    }


    /**
     * import brands from csv file.
     *
     *
     * @return bool
     */
    public function import(Request $request)
    {
        // $validator = Validator::make(request()->all(), [
        //     'attributes' => 'required|mimes:csv,txt,application/vnd.ms-excel',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'message'   => $validator->messages()->first(),
        //     ], 200);
        // }

        $file = $request->file('attributes');
        $data = Excel::toArray([], $file);
        $header = array_shift($data[0]);

        $locales = ['en', 'ar'];
        foreach ($data[0] as $row) {
            $attribute_option_data = array_combine($header, $row);
            if (isset($attribute_option_data['image'])) {
                $image = $attribute_option_data['image'];
                unset($attribute_option_data['image']);
            }
            $attribute = $this->attributeRepository->where('code', strtolower($request->input('attribute_name')))->first();

            $attribute_option = $this->attributeOptionRepository->where(['admin_name' => $attribute_option_data["slug"], 'attribute_id' => $attribute->id])->first();

            $data = [
                "admin_name" => $attribute_option_data["slug"],
                "sort_order" => $attribute_option_data["position"] ?? 0,
            ];

            foreach ($locales as $locale) {
                $data[$locale] = [
                    "label" => $attribute_option_data["label_$locale"],
                ];
            }

            if (!$attribute_option) {
                $attribute_option = $this->attributeOptionRepository->create(array_merge([
                    'attribute_id' => $attribute->id,
                ], $data));
            } else {
                $attribute_option = $this->attributeOptionRepository->update($data, $attribute_option->id);
            }

            // if (isset($image) && $image == 1) {
            //     $attribute_option->swatch_value = 'attribute_option/' . $attribute_option_data['slug'] . '.png';
            //     $attribute_option->save();
            // }
            if (isset($image)) {
                $client = new Client();
                $response = $client->get($image);
                $imageContent = $response->getBody()->getContents();
                $path = 'attribute_option/' . $attribute_option_data['slug'] . '.png';
                Storage::disk('public')->put($path, $imageContent);
                $attribute_option->swatch_value = $path;
                $attribute_option->save();
            }
        }

        session()->flash('success', 'تم الإستيراد بنجاح');

        return response()->json(['message' => 'تم الإستيراد بنجاح']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     *
     * @return \Illuminate\View\View
     */
    public function brandIndex()
    {
        $attribute = $this->attributeRepository->getAttributeByCode('brand');

        return view('admin::catalog.attributes.edit', compact('attribute'));
    }
}
