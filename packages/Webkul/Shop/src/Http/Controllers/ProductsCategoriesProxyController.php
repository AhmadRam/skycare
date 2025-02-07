<?php

namespace Webkul\Shop\Http\Controllers;

use Webkul\Shop\Jobs\SendFacebookEventJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use stdClass;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Blog\Models\Blog;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Marketing\Repositories\URLRewriteRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class ProductsCategoriesProxyController extends Controller
{
    /**
     * Using const variable for status
     *
     * @var int Status
     */
    const STATUS = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected ThemeCustomizationRepository $themeCustomizationRepository,
        protected URLRewriteRepository $urlRewriteRepository,
        protected AttributeOptionRepository $attributeOptionsRepository,
    ) {}

    /**
     * Show product or category view. If neither category nor product matches, abort with code 404.
     *
     * @return \Illuminate\View\View|\Exception
     */
    public function index(Request $request)
    {
        $slugOrURLKey = urldecode(trim($request->getPathInfo(), '/'));

        /**
         * Support url for chinese, japanese, arabic and english with numbers.
         */
        if (!preg_match('/^([\x{0621}-\x{064A}\x{4e00}-\x{9fa5}\x{3402}-\x{FA6D}\x{3041}-\x{30A0}\x{30A0}-\x{31FF}_a-z0-9-]+\/?)+$/u', $slugOrURLKey)) {
            visitor()->visit();

            $customizations = $this->themeCustomizationRepository->orderBy('sort_order')->findWhere([
                'status'     => self::STATUS,
                'channel_id' => core()->getCurrentChannel()->id,
            ]);

            return view('shop::home.index', compact('customizations'));
        }

        $category = $this->categoryRepository->findBySlug($slugOrURLKey);

        if ($category) {
            visitor()->visit($category);
            $brands = app(AttributeRepository::class)->getAttributeByCode('brand')->options;

            if ($slugOrURLKey == 'brands') {
                return view('shop::brands.view', [
                    'category' => $category,
                    'brands' => $brands
                ]);
            }

            return view('shop::categories.view', [
                'category' => $category,
                'params'   => [
                    'sort'  => request()->query('sort'),
                    'limit' => request()->query('limit'),
                    'mode'  => request()->query('mode'),
                ],
            ]);
        }

        $product = $this->productRepository->findBySlug($slugOrURLKey);

        if ($product) {
            if (
                !$product->url_key
                || !$product->visible_individually
                || !$product->status
            ) {
                abort(404);
            }

            visitor()->visit($product);

            dispatch(new SendFacebookEventJob('ViewContent', auth()->user(), $product));

            $related_blogs = Blog::orderBy('id', 'desc')->where('status', 1)->paginate(10);

            return view('shop::products.view', compact('product', 'related_blogs'));
        }

        /**
         * If category is not found, try to find it by slug.
         * If category is found by slug, redirect to category path.
         */
        $trimmedSlug = last(explode('/', $slugOrURLKey));

        $category = $this->categoryRepository->findBySlug($trimmedSlug);

        if ($category) {
            return redirect()->to($trimmedSlug, 301);
        }

        /**
         * If neither category nor product matches,
         * try to find it by url rewrite for category.
         */
        $categoryURLRewrite = $this->urlRewriteRepository->findOneWhere([
            'entity_type'  => 'category',
            'request_path' => $slugOrURLKey,
            'locale'       => app()->getLocale(),
        ]);

        if ($categoryURLRewrite) {
            return redirect()->to($categoryURLRewrite->target_path, $categoryURLRewrite->redirect_type);
        }

        /**
         * If neither category nor product matches,
         * try to find it by url rewrite for product.
         */
        $productURLRewrite = $this->urlRewriteRepository->findOneWhere([
            'entity_type'  => 'product',
            'request_path' => $slugOrURLKey,
        ]);

        if ($productURLRewrite) {
            return redirect()->to($productURLRewrite->target_path, $productURLRewrite->redirect_type);
        }

        $brand = $this->attributeOptionsRepository->where('admin_name', $slugOrURLKey)->first();

        if ($brand) {
            $new_brand = new stdClass();
            $new_brand->id = 6;
            $new_brand->position = $brand->sort_order;
            $new_brand->logo_path = "";
            $new_brand->status = $brand->status;
            $new_brand->display_mode = "products_and_description";
            $new_brand->_lft = 0;
            $new_brand->_rgt = 0;
            $new_brand->parent_id = 1;
            $new_brand->additional = null;
            $new_brand->banner_path = null;
            $new_brand->name = $brand->label;
            $new_brand->description = '';
            $new_brand->slug = strtolower($brand->label);
            $new_brand->meta_title = $brand->meta_title;
            $new_brand->meta_description = $brand->meta_description;
            $new_brand->meta_keywords = $brand->meta_keywords;
            $new_brand->is_brand = true;

            // visitor()->visit($brand);

            return view('shop::categories.view', [
                'category' => $new_brand,
                'params'   => [
                    'sort'  => request()->query('sort'),
                    'limit' => request()->query('limit'),
                    'mode'  => request()->query('mode'),
                ],
            ]);
        }

        abort(404);
    }
}
