<!-- SEO Meta Content -->
@push('meta')
    <style>
        .spinner-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            /* Light border */
            border-top-color: #3498db;
            /* Blue color */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
@push('meta')

    <meta name="title" content="{{ trim($category->meta_title) != '' ? $category->meta_title : $category->name }}" />

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

    {!! view_render_event('bagisto.shop.categories.view.description.before') !!}

    <div class="container mt-8 px-[60px] max-lg:px-8 max-sm:px-4">
        <h1>{{ $category->name }}</h1>
    </div>

    @if (in_array($category->display_mode, [null, 'description_only', 'products_and_description']))
        @if ($category->description && strip_tags($category->description) != $category->name)
            <div class="container mt-8 px-[60px] max-lg:px-8 max-sm:px-4">
                {!! $category->description !!}
            </div>
        @endif
    @endif

    {!! view_render_event('bagisto.shop.categories.view.description.after') !!}

    @if (in_array($category->display_mode, [null, 'products_only', 'products_and_description']))
        <!-- Category Vue Component -->
        <v-category>
            <!-- Category Shimmer Effect -->
            <x-shop::shimmer.categories.view />
        </v-category>
    @endif
    @pushOnce('scripts')
        <script
        type="text/x-template"
        id="v-category-template"
    >
        <div class="container px-[60px] max-lg:px-8 max-sm:px-4">
            <div class="flex gap-10 items-start md:mt-10 max-lg:gap-5">
                <!-- Product Listing Filters -->
                @include('shop::categories.filters')

                <!-- Product Listing Container -->
                <div class="flex-1">
                    <!-- Desktop Product Listing Toolbar -->
                    <div class="max-md:hidden">
                        @include('shop::categories.toolbar')
                    </div>

                    <!-- Product List Card Container -->
                    <div
                        class="grid grid-cols-1 gap-6 mt-8"
                        v-if="filters.toolbar.mode === 'list'"
                    >
                        <!-- Product Card Shimmer Effect -->
                        <template v-if="isLoading">
                            <x-shop::shimmer.products.cards.list count="12" />
                        </template>

                        <!-- Product Card Listing -->
                        {!! view_render_event('bagisto.shop.categories.view.list.product_card.before') !!}

                        <template v-else>
                            <template v-if="products.length">
                                <x-shop::products.card
                                    ::mode="'list'"
                                    v-for="product in products"
                                />
                            </template>

                            <!-- Empty Products Container -->
                            <template v-else>
                                <div class="grid items-center justify-items-center place-content-center w-full m-auto h-[476px] text-center">
                                    <img
                                        src="{{ bagisto_asset('images/thank-you.png') }}"
                                        alt="@lang('shop::app.categories.view.empty')"
                                    />
                                    <p class="text-xl" role="heading">
                                        @lang('shop::app.categories.view.empty')
                                    </p>
                                </div>
                            </template>
                        </template>

                        {!! view_render_event('bagisto.shop.categories.view.list.product_card.after') !!}
                    </div>

                    <!-- Product Grid Card Container -->
                    <div v-else class="mt-8">
                        <!-- Product Card Shimmer Effect -->
                        <template v-if="isLoading">
                            <div class="grid grid-cols-3 gap-8 max-1060:grid-cols-2 max-sm:justify-items-center max-sm:gap-4">
                                <x-shop::shimmer.products.cards.grid count="12" />
                            </div>
                        </template>

                        {!! view_render_event('bagisto.shop.categories.view.grid.product_card.before') !!}

                        <!-- Product Card Listing -->
                        <template v-else>
                            <template v-if="products.length">
                                <div class="grid grid-cols-3 gap-8 max-1060:grid-cols-2 max-sm:justify-items-center max-sm:gap-4">
                                    <x-shop::products.card
                                        ::mode="'grid'"
                                        v-for="product in products"
                                    />
                                </div>
                            </template>

                            <!-- Empty Products Container -->
                            <template v-else>
                                <div class="grid items-center justify-items-center place-content-center w-full m-auto h-[476px] text-center">
                                    <img
                                        src="{{ bagisto_asset('images/thank-you.png') }}"
                                        alt="@lang('shop::app.categories.view.empty')"
                                    />
                                    <p class="text-xl" role="heading">
                                        @lang('shop::app.categories.view.empty')
                                    </p>
                                </div>
                            </template>
                        </template>

                        {!! view_render_event('bagisto.shop.categories.view.grid.product_card.after') !!}
                    </div>

                    {!! view_render_event('bagisto.shop.categories.view.load_more_button.before') !!}

                    <!-- Loading Spinner (visible while fetching) -->
                    <div class="spinner-container" v-if="isFetching && !isLoading">
                        <div class="spinner"></div>
                    </div>

                    {!! view_render_event('bagisto.shop.categories.view.grid.load_more_button.after') !!}
                </div>
            </div>
        </div>
    </script>

        <script type="module">
            app.component('v-category', {
                template: '#v-category-template',

                data() {
                    return {
                        isMobile: window.innerWidth <= 767,
                        isLoading: true,
                        isDrawerActive: {
                            toolbar: false,
                            filter: false
                        },
                        filters: {
                            toolbar: {},
                            filter: {}
                        },
                        products: [],
                        links: {},
                        loader: false,
                        isFetching: false, // Prevent multiple calls during scrolling
                    };
                },

                computed: {
                    queryParams() {
                        return this.removeJsonEmptyValues(Object.assign({}, this.filters.filter, this.filters.toolbar));
                    },

                    queryString() {
                        return this.jsonToQueryString(this.queryParams);
                    },
                },

                watch: {
                    queryParams() {
                        this.getProducts();
                    },

                    queryString() {
                        window.history.pushState({}, '', '?' + this.queryString);
                    },
                },

                mounted() {
                    this.getProducts();

                    // Add scroll event listener
                    window.addEventListener('scroll', this.handleScroll);
                },

                beforeUnmount() {
                    // Clean up event listener to prevent memory leaks
                    window.removeEventListener('scroll', this.handleScroll);
                },

                methods: {
                    handleScroll() {
                        const bottomOfList = this.$el.getBoundingClientRect().bottom;
                        const windowHeight = window.innerHeight;

                        if (bottomOfList <= windowHeight && this.links.next && !this.isFetching) {
                            this.loadMoreProducts();
                        }
                    },

                    setFilters(type, filters) {
                        this.filters[type] = filters;
                    },

                    clearFilters(type) {
                        this.filters[type] = {};
                    },

                    getProducts() {
                        this.isLoading = true;
                        const categoryId = "{{ !$category->is_brand ? $category->id : '' }}";
                        const apiUrl = `{{ route('shop.api.products.index') }}?category_id=${categoryId}`;

                        this.$axios
                            .get(apiUrl, {
                                params: this.queryParams
                            })
                            .then(response => {
                                this.isLoading = false;
                                this.products = response.data.data;
                                this.links = response.data.links;
                            })
                            .catch(error => console.error(error));
                    },

                    loadMoreProducts() {
                        if (!this.links.next) return;

                        this.isFetching = true; // Prevent multiple requests
                        this.$axios
                            .get(this.links.next)
                            .then(response => {
                                this.products = [...this.products, ...response.data.data];
                                this.links = response.data.links;
                                this.isFetching = false;
                            })
                            .catch(error => {
                                console.error(error);
                                this.isFetching = false;
                            });
                    },

                    removeJsonEmptyValues(params) {
                        Object.keys(params).forEach(key => {
                            if (!params[key] && params[key] !== undefined) delete params[key];
                            if (Array.isArray(params[key])) params[key] = params[key].join(',');
                        });
                        return params;
                    },

                    jsonToQueryString(params) {
                        let parameters = new URLSearchParams();
                        for (const key in params) parameters.append(key, params[key]);
                        return parameters.toString();
                    },
                },
            });
        </script>
    @endPushOnce

</x-shop::layouts>
