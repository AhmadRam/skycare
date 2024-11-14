<?php

namespace Webkul\Admin\Http\Controllers\Catalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\DataGrids\Catalog\ProductDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\InventoryRequest;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Admin\Http\Requests\ProductForm;
use Webkul\Admin\Http\Resources\AttributeResource;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Core\Rules\Slug;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Product\Helpers\ProductType;
use Webkul\Product\Repositories\ProductAttributeValueRepository;
use Webkul\Product\Repositories\ProductDownloadableLinkRepository;
use Webkul\Product\Repositories\ProductDownloadableSampleRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Webkul\Admin\Http\Resources\ProductResource;
use Webkul\Product\Exports\ProductsExport;
use Webkul\Product\Models\ProductImage;

class ProductController extends Controller
{
    /*
    * Using const variable for status
    */
    const ACTIVE_STATUS = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeFamilyRepository $attributeFamilyRepository,
        protected InventorySourceRepository $inventorySourceRepository,
        protected ProductAttributeValueRepository $productAttributeValueRepository,
        protected ProductDownloadableLinkRepository $productDownloadableLinkRepository,
        protected ProductDownloadableSampleRepository $productDownloadableSampleRepository,
        protected ProductInventoryRepository $productInventoryRepository,
        protected ProductRepository $productRepository,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(ProductDataGrid::class)->toJson();
        }

        $families = $this->attributeFamilyRepository->all();

        return view('admin::catalog.products.index', compact('families'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $families = $this->attributeFamilyRepository->all();

        $configurableFamily = null;

        if ($familyId = request()->get('family')) {
            $configurableFamily = $this->attributeFamilyRepository->find($familyId);
        }

        return view('admin::catalog.products.create', compact('families', 'configurableFamily'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $this->validate(request(), [
            'type'                => 'required',
            'attribute_family_id' => 'required',
            'sku'                 => ['required', 'unique:products,sku', new Slug],
            'super_attributes'    => 'array|min:1',
            'super_attributes.*'  => 'array|min:1',
        ]);

        if (
            ProductType::hasVariants(request()->input('type'))
            && !request()->has('super_attributes')
        ) {
            $configurableFamily = $this->attributeFamilyRepository
                ->find(request()->input('attribute_family_id'));

            return new JsonResponse([
                'data' => [
                    'attributes' => AttributeResource::collection($configurableFamily->configurable_attributes),
                ],
            ]);
        }

        Event::dispatch('catalog.product.create.before');

        $product = $this->productRepository->create(request()->only([
            'type',
            'attribute_family_id',
            'sku',
            'super_attributes',
            'family',
        ]));

        Event::dispatch('catalog.product.create.after', $product);

        session()->flash('success', trans('admin::app.catalog.products.create-success'));

        return new JsonResponse([
            'data' => [
                'redirect_url' => route('admin.catalog.products.edit', $product->id),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $product = $this->productRepository->findOrFail($id);

        $inventorySources = $this->inventorySourceRepository->findWhere(['status' => self::ACTIVE_STATUS]);

        return view('admin::catalog.products.edit', compact('product', 'inventorySources'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(ProductForm $request, int $id)
    {
        Event::dispatch('catalog.product.update.before', $id);

        $product = $this->productRepository->update(request()->all(), $id);

        Event::dispatch('catalog.product.update.after', $product);

        session()->flash('success', trans('admin::app.catalog.products.update-success'));

        return redirect()->route('admin.catalog.products.index');
    }

    /**
     * Update inventories.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateInventories(InventoryRequest $inventoryRequest, int $id)
    {
        $product = $this->productRepository->findOrFail($id);

        Event::dispatch('catalog.product.update.before', $id);

        $this->productInventoryRepository->saveInventories(request()->all(), $product);

        Event::dispatch('catalog.product.update.after', $product);

        return response()->json([
            'message'      => __('admin::app.catalog.products.saved-inventory-message'),
            'updatedTotal' => $this->productInventoryRepository->where('product_id', $product->id)->sum('qty'),
        ]);
    }

    /**
     * Uploads downloadable file.
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadLink(int $id)
    {
        return response()->json(
            $this->productDownloadableLinkRepository->upload(request()->all(), $id)
        );
    }

    /**
     * Copy a given Product.
     *
     * @return \Illuminate\Http\Response
     */
    public function copy(int $id)
    {
        try {
            $product = $this->productRepository->copy($id);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->to(route('admin.catalog.products.index'));
        }

        session()->flash('success', trans('admin::app.catalog.products.product-copied'));

        return redirect()->route('admin.catalog.products.edit', $product->id);
    }

    /**
     * Uploads downloadable sample file.
     *
     * @return \Illuminate\Http\Response
     */
    public function uploadSample(int $id)
    {
        return response()->json(
            $this->productDownloadableSampleRepository->upload(request()->all(), $id)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            Event::dispatch('catalog.product.delete.before', $id);

            $this->productRepository->delete($id);

            Event::dispatch('catalog.product.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.catalog.products.delete-success'),
            ]);
        } catch (\Exception $e) {
            report($e);
        }

        return new JsonResponse([
            'message' => trans('admin::app.catalog.products.delete-failed'),
        ], 500);
    }

    /**
     * Mass delete the products.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $productIds = $massDestroyRequest->input('indices');

        try {
            foreach ($productIds as $productId) {
                $product = $this->productRepository->find($productId);

                if (isset($product)) {
                    Event::dispatch('catalog.product.delete.before', $productId);

                    $this->productRepository->delete($productId);

                    Event::dispatch('catalog.product.delete.after', $productId);
                }
            }

            return new JsonResponse([
                'message' => trans('admin::app.catalog.products.index.datagrid.mass-delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mass update the products.
     */
    public function massUpdate(MassUpdateRequest $massUpdateRequest): JsonResponse
    {
        $data = $massUpdateRequest->all();

        $productIds = $data['indices'];

        foreach ($productIds as $productId) {
            Event::dispatch('catalog.product.update.before', $productId);

            $product = $this->productRepository->update([
                'status'  => $massUpdateRequest->input('value'),
            ], $productId);

            Event::dispatch('catalog.product.update.after', $product);
        }

        return new JsonResponse([
            'message' => trans('admin::app.catalog.products.index.datagrid.mass-update-success'),
        ], 200);
    }

    /**
     * To be manually invoked when data is seeded into products.
     *
     * @return \Illuminate\Http\Response
     */
    public function sync()
    {
        Event::dispatch('products.datagrid.sync', true);

        return redirect()->route('admin.catalog.products.index');
    }

    /**
     * Result of search product.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        $results = [];

        request()->query->add([
            'status'               => null,
            'visible_individually' => null,
            'name'                 => request('query'),
            'sort'                 => 'created_at',
            'order'                => 'desc',
        ]);

        $products = $this->productRepository->searchFromDatabase();

        return ProductResource::collection($products);

        // foreach ($products as $product) {
        //     $results[] = [
        //         'id'              => $product->id,
        //         'sku'             => $product->sku,
        //         'name'            => $product->name,
        //         'price'           => $product->price,
        //         'formatted_price' => core()->formatBasePrice($product->price),
        //         'images'          => $product->images,
        //         'inventories'     => $product->inventories,
        //     ];
        // }

        // $products->setCollection(collect($results));

        // return response()->json($products);
    }

    /**
     * Download image or file.
     *
     * @param  int  $productId
     * @param  int  $attributeId
     * @return \Illuminate\Http\Response
     */
    public function download($productId, $attributeId)
    {
        $productAttribute = $this->productAttributeValueRepository->findOneWhere([
            'product_id'   => $productId,
            'attribute_id' => $attributeId,
        ]);

        return Storage::download($productAttribute['text_value']);
    }


    /**
     * import categories from csv file.
     *
     *
     * @return bool
     */
    public function import(Request $request)
    {
        // $request->validate([
        //     'products' => 'required|mimes:csv',
        // ]);

        $file = $request->file('products');
        $data = Excel::toArray([], $file);
        $header = array_shift($data[0]);
        $header[34] = 'variants';

        $brand_attribute = $this->attributeRepository->where('code', 'brand')->first();
        $option_attribute = $this->attributeRepository->where('code', 'option')->first();

        $locales = ['en', 'ar'];
        foreach ($data[0] as $row) {
            $categories_ids = [];
            $product_data = array_combine($header, $row);

            if (isset($product_data['image'])) {
                $image = $product_data['image'];
                unset($product_data['image']);
            }
            if ($product_data['sku'] != "") {

                $product = $this->productRepository->where('sku', $product_data['sku'])->first();

                $data = [
                    "type" => $product_data["type"],
                    "attribute_family_id" => 1,
                    "sku" => $product_data["sku"]
                ];

                if ($product_data["type"] != 'simple') {
                    $data['family'] = '1';
                    foreach (explode(' - ', $product_data["variants"]) as $option) {
                        $option = explode(',', $option);
                        $db_option = $this->attributeOptionRepository->whereRaw('LOWER(admin_name) = ?', strtolower($option[1]))->where('attribute_id', $option_attribute->id)->first();
                        if ($db_option) {
                            $data['super_attributes']['option'][] = $db_option->id;
                        }
                    }
                }

                if (!$product) {
                    Event::dispatch('catalog.product.create.before');

                    $product = $this->productRepository->create($data);

                    Event::dispatch('catalog.product.create.after', $product);
                }

                foreach (explode(',', $product_data["categories_slug"]) as $category_slug) {
                    $category = $this->categoryRepository->findBySlug($category_slug);
                    if ($category) {
                        $categories_ids[] = $category->id;
                    }
                }

                $brand = $this->attributeOptionRepository->whereRaw('LOWER(admin_name) = ?', strtolower($product_data["brand"]))->where('attribute_id', $brand_attribute->id)->first();

                foreach ($locales as $locale) {
                    $data = [
                        "locale" => $locale,
                        "sku" => $product_data["sku"],
                        "product_number" => $product_data["product_number"],
                        "name" => $product_data["name_$locale"],
                        "url_key" => $product_data["url_key"],
                        "new" => $product_data["new"],
                        "tax_category_id" => "",
                        "featured" => $product_data["featured"],
                        "visible_individually" => $product_data["visible_individually"],
                        "guest_checkout" => $product_data["guest_checkout"],
                        "status" => $product_data["status"],
                        "description" => $product_data["description_$locale"],
                        "short_description" => $product_data["short_description_$locale"],
                        "meta_title" => $product_data["meta_title_$locale"],
                        "meta_description" => $product_data["meta_description_$locale"],
                        "meta_keywords" => $product_data["meta_keywords_$locale"],
                        "price" => $product_data["price"],
                        "cost" => $product_data["cost"],
                        "special_price" => $product_data["special_price"],
                        "special_price_from" => $product_data["special_price_from"] ?? "",
                        "special_price_to" => $product_data["special_price_to"] ?? "",
                        "length" => $product_data["length"],
                        "width" => $product_data["width"],
                        "height" => $product_data["height"],
                        "weight" => $product_data["weight"],
                        "inventories" => [1 => $product_data["inventories"]],
                        "channels" => [1],
                        "channel" => 'default',
                        "categories" => $categories_ids,
                        "brand" =>  $brand->id ?? null,
                    ];

                    // if ($product_data["type"] == 'simple') {
                    //     $option = $this->attributeOptionRepository->whereRaw('LOWER(admin_name) = ?', strtolower($product_data["option"]))->where('attribute_id', $option_attribute->id)->first();
                    //     $data["option"] = $option->id ?? null;
                    // }

                    if ($product_data["type"] != 'simple') {
                        foreach (explode(' - ', $product_data["variants"]) as $variant) {
                            $variant = explode(',', $variant);
                            $db_variant = $this->productRepository->where('parent_id', $product->id)->where('sku', $variant[0])->first();
                            $db_option = $this->attributeOptionRepository->whereRaw('LOWER(admin_name) = ?', strtolower($variant[1]))->where('attribute_id', $option_attribute->id)->first();
                            if ($db_option) {
                                $data['variants'][$db_variant ? $db_variant->id : "variant_$variant[0]"] = [
                                    "sku" => $variant[0],
                                    "name" =>  $product_data["name_$locale"],
                                    "option" => $db_option->id,
                                    "inventories" => [1 => $variant[2]],
                                    "price" => $variant[3],
                                    "weight" => $variant[4],
                                    "status" => $variant[5],
                                ];
                            }
                        }
                    }

                    Event::dispatch('catalog.product.update.before', $product->id);

                    $product = $this->productRepository->update($data, $product->id);

                    Event::dispatch('catalog.product.update.after', $product);
                }

                if (isset($image)) {
                    for ($i = 1; $i <= $image; $i++) {
                        $path = 'product/' . $product_data['sku'] . '/' . $product_data['sku'] . '_' . $i . '.png';
                        if (!ProductImage::where(['path' => $path, 'product_id' => $product->id])->first()) {
                            ProductImage::create([
                                'type'       => 'images',
                                'path'       => $path,
                                'product_id' => $product->id,
                                'position'   => $i,
                            ]);
                        }
                        if ($product_data["type"] != 'simple') {
                            foreach ($product->variants as $variant) {
                                ProductImage::create([
                                    'type'       => 'images',
                                    'path'       => $path,
                                    'product_id' => $variant->id,
                                    'position'   => $i,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        session()->flash('success', 'تم الإستيراد بنجاح');

        return response()->json(['message' => 'تم الإستيراد بنجاح']);
    }

    /**
     * export to csv file.
     *
     *
     * @return bool
     */
    public function export()
    {
        return Excel::download(new ProductsExport(), 'products.xlsx');
    }
}
