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
use Illuminate\Support\Facades\Cache;

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
    ) {}

    /**
     * Get all categories.
     */
    public function index(): JsonResource
    {

        // Define a unique cache key for this function
        $cacheKey = 'category_index_' . core()->getRequestedLocaleCode() . '_' . core()->getCurrentChannelCode();

        // Attempt to retrieve the cached data
        $categories = Cache::rememberForever($cacheKey, function () {
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
            return $categories;
        });

        return CategoryResource::collection($categories);
    }

    /**
     * Get all categories in tree format.
     */
    public function tree(): JsonResource
    {
        // Define a unique cache key for this function
        $cacheKey = 'category_tree_' . core()->getRequestedLocaleCode() . '_' . core()->getCurrentChannelCode();

        // Attempt to retrieve the cached data
        $categories = Cache::rememberForever($cacheKey, function () {
            // If the data is not in the cache, execute the original logic
            $categories = $this->categoryRepository->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id);

            $attribute = $this->attributeRepository->getAttributeByCode('brand');

            $parentObject = new stdClass;
            $parentObject->id = 1000;
            $parentObject->parent_id = 1;
            $parentObject->name = __('admin::app.components.layouts.sidebar.brands');
            $parentObject->slug = 'brands';
            $parentObject->url =  '/brands';
            $parentObject->status = true;
            $parentObject->children = [];

            foreach ($attribute->options as $brand) {
                $childObject = new stdClass;
                $childObject->id = $brand->id;
                $childObject->parent_id = 1;
                $childObject->name = $brand->label;
                $childObject->slug = $brand->admin_name . '?' . 'brand=' . $brand->id;
                $childObject->url = '/' . $brand->admin_name . '?' . 'brand=' . $brand->id;
                $childObject->status = true;
                $childObject->children = [];

                $parentObject->children[] = $childObject;
            }

            $categories->push($parentObject);

            $blogObject = new stdClass;
            $blogObject->id = 13546;
            $blogObject->parent_id = 1;
            $blogObject->name = __('admin::app.components.layouts.sidebar.blog');
            $blogObject->slug = 'blog';
            $blogObject->url = '/blog';
            $blogObject->status = true;
            $blogObject->children = [];

            $categories->push($blogObject);

            return $categories;
        });

        return CategoryTreeResource::collection($categories);
    }

    /**
     * Get filterable attributes for category.
     */
    public function getAttributes(): JsonResource
    {
        // Define a unique cache key based on the category_id (if provided)
        $cacheKey = 'filterable_attributes_' . (request('category_id') ?? 'all') . '_' . core()->getRequestedLocaleCode() . '_' . core()->getCurrentChannelCode();

        // Attempt to retrieve the cached data
        $filterableAttributes = Cache::remember($cacheKey, now()->addHours(24), function () {
            if (!request('category_id')) {
                return $this->attributeRepository->getFilterableAttributes();
            }

            $category = $this->categoryRepository->findOrFail(request('category_id'));

            if (empty($filterableAttributes = $category->filterableAttributes)) {
                return $this->attributeRepository->getFilterableAttributes();
            }

            return $filterableAttributes;
        });

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
