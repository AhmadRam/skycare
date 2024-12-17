<?php

namespace Webkul\Product\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Product\Models\Product;
use Webkul\Product\Facades\ProductImage;

class ProductsSeoExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $locale = 'en';

    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    public function query()
    {
        $query = Product::query();

        return $query;
    }

    public function headings(): array
    {
        return [
            'id',
            'title',
            'description',
            'link',
            'availability',
            'price',
            'image_link',
            'brand',
            'condition',
        ];
    }

    public function map($product): array
    {
        app()->setLocale($this->locale);
        $productTypeInstance = $product->getTypeInstance();
        $specialPrice = $productTypeInstance->getMinimalPrice();
        $brand = app(AttributeOptionRepository::class)->find($product->brand);

        return [
            $product->sku,
            $product->name,
            $product->description,
            route('shop.product_or_category.index', $product->url_key),
            $product->totalQuantity() == 0 ? 'out of stock' : 'in stock',
            $specialPrice,
            ProductImage::getProductBaseImage($product)['original_image_url'] ?? '',
            $brand->label ?? "NA",
            "NEW",
        ];
    }
}
