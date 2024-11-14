<?php

namespace Webkul\Product\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Product\Models\Product;
use Webkul\Product\Facades\ProductImage;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        $query = Product::query();

        return $query;
    }

    public function headings(): array
    {
        return [
            'sku',
            'English Name',
            'Arabic Name',
            'Product Number',
            'cost',
            'sale_price',
        ];
    }

    public function map($product): array
    {
        $productTypeInstance = $product->getTypeInstance();
        $sale_price = $productTypeInstance->getMinimalPrice();

        return [
            $product->sku,
            $product->product_flats->where('locale', 'en')->first()->name ?? null,
            $product->product_flats->where('locale', 'ar')->first()->name ?? null,
            $product->product_number,
            $product->cost,
            $sale_price,
        ];
    }
}
