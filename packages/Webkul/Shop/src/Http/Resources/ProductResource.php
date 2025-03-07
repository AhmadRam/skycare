<?php

namespace Webkul\Shop\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Webkul\Product\Helpers\Review;

class ProductResource extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->reviewHelper = app(Review::class);

        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $cacheKey = 'product_' . app()->getLocale() . '_' . core()->getCurrentCurrencyCode() . '_' . core()->getRequestedChannelCode() . '_' . ($this->product_id ?? $this->id) . ($request->with_sales ? '_with_sales' : null);

        return Cache::rememberForever($cacheKey, function () use ($cacheKey, $request) {
            Redis::sadd('product_keys', $cacheKey);
            $productTypeInstance = $this->getTypeInstance();

            return [
                'id'          => $this->id,
                'sku'         => $this->sku,
                'name'        => $this->name,
                'description' => $this->description,
                'url_key'     => $this->url_key,
                'base_image'  => product_image()->getProductBaseImage($this),
                'images'      => product_image()->getGalleryImages($this),
                'is_new'      => (bool) $this->new,
                'is_featured' => (bool) $this->featured,
                'is_top_sell' => (bool) $this->top_sell,
                'on_sale'     => (bool) $productTypeInstance->haveDiscount(),
                'is_saleable' => (bool) $productTypeInstance->isSaleable(),
                'is_wishlist' => (bool) auth()->guard()->user()?->wishlist_items
                    ->where('channel_id', core()->getCurrentChannel()->id)
                    ->where('product_id', $this->id)->count(),
                'min_price'   => core()->formatPrice($productTypeInstance->getMinimalPrice()),
                'prices'      => $productTypeInstance->getProductPrices(),
                'price_html'  => $productTypeInstance->getPriceHtml(),
                'avg_ratings' => round($this->reviewHelper->getAverageRating($this)),
            ];
        });
    }
}
