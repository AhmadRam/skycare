<!-- SEO Meta Content -->
@push('meta')
    <meta name="description"
        content="{{ trim($category->meta_description) != '' ? $category->meta_description : \Illuminate\Support\Str::limit(strip_tags($category->description), 120, '') }}" />

    <meta name="keywords" content="{{ $category->meta_keywords }}" />

    @if (core()->getConfigData('catalog.rich_snippets.categories.enable'))
        <script type="application/ld+json">
            {!! app('Webkul\Product\Helpers\SEO')->getCategoryJsonLd($category) !!}
        </script>
    @endif
@endPush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ trim($category->meta_title) != '' ? $category->meta_title : $category->name }}
    </x-slot>

    {!! view_render_event('bagisto.shop.categories.view.banner_path.before') !!}

    <!-- Hero Image -->
    @if ($category->banner_path)
        <div class="container mt-8 px-[60px] max-lg:px-8 max-sm:px-4">
            <div>
                <img class="rounded-xl" src="{{ $category->banner_url }}" alt="{{ $category->name }}" width="1320"
                    height="300">
            </div>
        </div>
    @endif

    {!! view_render_event('bagisto.shop.categories.view.banner_path.after') !!}

    @push('styles')
        <style>
            .top-collection-container {
                overflow: hidden;
            }

            .top-collection-header {
                padding-left: 15px;
                padding-right: 15px;
                text-align: center;
                font-size: 70px;
                line-height: 90px;
                color: #060C3B;
                margin-top: 80px;
            }

            .top-collection-header h2 {
                max-width: 595px;
                margin-left: auto;
                margin-right: auto;
                font-family: DM Serif Display;
            }

            .top-collection-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 32px;
                justify-content: center;
                margin-top: 60px;
                width: 100%;
                margin-right: auto;
                margin-left: auto;
                padding-right: 90px;
                padding-left: 90px;
            }

            .top-collection-card {
                text-align: -webkit-center;
                position: relative;
                background: #f9fafb;
                overflow: hidden;
                border-radius: 20px;
            }

            .top-collection-card img {
                border-radius: 16px;
                max-width: 100%;
                text-indent: -9999px;
            }

            .top-collection-card:hover img {
                transform: scale(1.05);
                transition: all 300ms ease;
            }

            .top-collection-card h3 {
                color: #060C3B;
                font-size: 30px;
                font-family: DM Serif Display;
                /* transform: translateX(-50%); */
                width: max-content;
                left: 50%;
                bottom: 0px;
                /* position: relative; */
                margin: 0;
                font-weight: inherit;
            }

            @media not all and (min-width: 525px) {
                .top-collection-header {
                    margin-top: 30px;
                }

                .top-collection-header {
                    font-size: 32px;
                    line-height: 1.5;
                }

                .top-collection-grid {
                    gap: 15px;
                }
            }

            @media not all and (min-width: 1024px) {
                .top-collection-grid {
                    padding-left: 30px;
                    padding-right: 30px;
                }
            }

            @media (max-width: 640px) {
                .top-collection-grid {
                    margin-top: 20px;
                }
            }
        </style>
    @endpush

    <div class="top-collection-container">
        <div class="top-collection-header">
            <h2>{!! $category->description !!}</h2>
        </div>
        <div class="container top-collection-grid">
            @foreach ($brands as $brand)
                <div class="top-collection-card">
                    <a href="/{{ $brand->admin_name . '?' . 'brand=' . $brand->id }}">
                        <img src="" data-src="{{ $brand->swatch_value_url }}" class="lazy" width="396"
                            height="396" alt="{{ $brand->label }}">
                        <h3>{{ $brand->label }}</h3>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

</x-shop::layouts>
