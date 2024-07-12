<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\Resources\Json\JsonResource;
use stdClass;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Resources\AttributeResource;
use Webkul\Shop\Http\Resources\CategoryResource;
use Webkul\Shop\Http\Resources\CategoryTreeResource;

class CategoryController extends APIController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
    ) {
    }

    /**
     * Get all categories.
     */
    public function index(): JsonResource
    {
        /**
         * These are the default parameters. By default, only the enabled category
         * will be shown in the current locale.
         */
        $defaultParams = [
            'status' => 1,
            'locale' => app()->getLocale(),
        ];

        $categories = $this->categoryRepository->getAll(array_merge($defaultParams, request()->all()));

        $brand = $this->categoryRepository->findBySlug('brands');
        $parentObject = new stdClass;
        $parentObject->id = 1000;
        $parentObject->parent_id = 1;
        $parentObject->name = __('admin::app.components.layouts.sidebar.brands');
        $parentObject->slug = $brand->slug ?? 'brands';
        $parentObject->url = 'brands';
        $parentObject->position = $brand->position ?? 0;
        $parentObject->display_mode = '';
        $parentObject->description = '';
        $parentObject->banner_path = $brand->banner_path ?? '';
        $parentObject->logo_path = $brand->logo_path ?? '';
        $parentObject->meta_title = '';
        $parentObject->meta_keywords = '';
        $parentObject->meta_description = '';
        $parentObject->status = true;
        $parentObject->children = [];

        $categories->prepend($parentObject);

        return CategoryResource::collection($categories);
    }

    /**
     * Get all categories in tree format.
     */
    public function tree(): JsonResource
    {
        $categories = $this->categoryRepository->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id);

        $attribute = $this->attributeRepository->getAttributeByCode('brand');

        $parentObject = new stdClass;
        $parentObject->id = 1000;
        $parentObject->parent_id = 1;
        $parentObject->name = __('admin::app.components.layouts.sidebar.brands');
        $parentObject->slug = 'brands';
        $parentObject->url = 'brands';
        $parentObject->status = true;
        $parentObject->children = [];

        foreach ($attribute->options as $brand) {
            $childObject = new stdClass;
            $childObject->id = $brand->id;
            $childObject->parent_id = 1;
            $childObject->name = $brand->label;
            $childObject->slug = $brand->admin_name . '?' . 'brand=' . $brand->id;
            $childObject->url = $brand->admin_name . '?' . 'brand=' . $brand->id;
            $childObject->status = true;
            $childObject->children = [];

            $parentObject->children[] = $childObject;
        }

        $categories->push($parentObject);

        return CategoryTreeResource::collection($categories);
    }

    /**
     * Get filterable attributes for category.
     */
    public function getAttributes(): JsonResource
    {
        if (!request('category_id')) {
            $filterableAttributes = $this->attributeRepository->getFilterableAttributes();

            return AttributeResource::collection($filterableAttributes);
        }

        $category = $this->categoryRepository->findOrFail(request('category_id'));

        if (empty($filterableAttributes = $category->filterableAttributes)) {
            $filterableAttributes = $this->attributeRepository->getFilterableAttributes();
        }

        return AttributeResource::collection($filterableAttributes);
    }

    /**
     * Get product maximum price.
     */
    public function getProductMaxPrice($categoryId = null): JsonResource
    {
        $maxPrice = $this->productRepository->getMaxPrice(['category_id' => $categoryId]);

        return new JsonResource([
            'max_price' => core()->convertPrice($maxPrice),
        ]);
    }
}
