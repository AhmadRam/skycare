<?php

namespace Webkul\Blog\Http\Controllers\Shop;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Webkul\Blog\Models\Blog;
use Webkul\Blog\Models\Category;
use Webkul\Blog\Models\Tag;
use Webkul\Core\Models\CoreConfig;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * Using const variable for status
     */
    const STATUS = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected ThemeCustomizationRepository $themeCustomizationRepository)
    {
        $this->_config = request('_config');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index($tag_slug)
    {
        $tag = Tag::where('slug', $tag_slug)->firstOrFail();

        $tag_id = ( $tag && isset($tag->id) ) ? $tag->id : 0;

        $paginate = app('Webkul\Blog\Http\Controllers\Shop\BlogController')->getConfigByKey('blog_post_per_page');
        $paginate = ( isset($paginate) && !empty($paginate) && is_null($paginate) ) ? (int)$paginate : 9;

        $blogs = Blog::orderBy('id', 'desc')->where('status', 1)->whereRaw('FIND_IN_SET(?, tags)', [$tag_id])->paginate($paginate);

        $categories = Category::where('status', 1)->get();

        $tags = app('Webkul\Blog\Http\Controllers\Shop\BlogController')->getTagsWithCount();

        $customizations = $this->themeCustomizationRepository->orderBy('sort_order')->findWhere([
            'status'     => self::STATUS,
            'channel_id' => core()->getCurrentChannel()->id
        ]);

        $show_categories_count = app('Webkul\Blog\Http\Controllers\Shop\BlogController')->getConfigByKey('blog_post_show_categories_with_count');
        $show_tags_count = app('Webkul\Blog\Http\Controllers\Shop\BlogController')->getConfigByKey('blog_post_show_tags_with_count');
        $show_author_page = app('Webkul\Blog\Http\Controllers\Shop\BlogController')->getConfigByKey('blog_post_show_author_page');

        $blog_seo_meta_title = app('Webkul\Blog\Http\Controllers\Shop\BlogController')->getConfigByKey('blog_seo_meta_title');
        $blog_seo_meta_keywords = app('Webkul\Blog\Http\Controllers\Shop\BlogController')->getConfigByKey('blog_seo_meta_keywords');
        $blog_seo_meta_description = app('Webkul\Blog\Http\Controllers\Shop\BlogController')->getConfigByKey('blog_seo_meta_description');

        return view($this->_config['view'], compact('blogs', 'categories', 'customizations', 'tag', 'tags', 'show_categories_count', 'show_tags_count', 'show_author_page', 'blog_seo_meta_title', 'blog_seo_meta_keywords', 'blog_seo_meta_description'));
    }

}
