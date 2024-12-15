@push('styles')
    <style>
        .loader {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #3498db;
        }
    </style>
@endpush


@php
    if (request()->has('query')) {
        $title = trans('shop::app.search.title', ['query' => request()->query('query')]);
    } else {
        $title = trans('shop::app.search.results');
    }
@endphp

<!-- SEO Meta Content -->
@push('meta')
    <meta name="title" content="{{ $title }}" />

    <meta name="description" content="{{ $title }}" />

    <meta name="keywords" content="{{ $title }}" />
@endPush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ $title }}
    </x-slot>

    <div class="container px-[60px] max-lg:px-8 max-sm:px-4">
        @if (request()->has('image-search'))
            @include('shop::search.images.results')
        @endif

        <div class="flex justify-between items-center mt-8">
            <h1 class="text-2xl font-medium">
                {{ $title }}
            </h1>
        </div>
    </div>

    <!-- Product Listing -->
    <v-search>
        <x-shop::shimmer.categories.view />
    </v-search>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-search-template"
            >
        <div class="container px-[60px] max-lg:px-8 max-sm:px-4">
            <div class="flex gap-10 items-start md:mt-10 max-lg:gap-5">
                @include('shop::categories.filters')

                <div class="flex-1">
                    <div class="max-md:hidden">
                        @include('shop::categories.toolbar')
                    </div>

                    <!-- Product List Card Container -->
                    <div class="grid grid-cols-1 gap-6 mt-8" v-if="filters.toolbar.mode === 'list'">
                        <template v-if="isLoading">
                            <x-shop::shimmer.products.cards.list count="12" />
                        </template>

                        <template v-else-if="products.length">
                            <x-shop::products.card
                                ::mode="'list'"
                                v-for="product in products"

                            />
                        </template>

                        <template v-else>
                            <div class="grid items-center justify-items-center place-content-center w-full m-auto h-[476px] text-center">
                                <img src="{{ bagisto_asset('images/thank-you.png') }}" />
                                <p class="text-xl">
                                    @lang('shop::app.categories.view.empty')
                                </p>
                            </div>
                        </template>
                    </div>

                    <div v-else>
                        <template v-if="isLoading">
                            <div class="grid grid-cols-3 gap-8 mt-8 max-sm:mt-5 max-1060:grid-cols-2 max-sm:justify-items-center max-sm:gap-4">
                                <x-shop::shimmer.products.cards.grid count="12" />
                            </div>
                        </template>

                        <template v-else-if="products.length">
                            <div class="grid grid-cols-3 gap-8 mt-8 max-sm:mt-5 max-1060:grid-cols-2 max-sm:justify-items-center max-sm:gap-4">
                                <x-shop::products.card
                                    ::mode="'grid'"
                                    v-for="product in products"

                                />
                            </div>
                        </template>

                        <template v-else>
                            <div class="grid items-center justify-items-center place-content-center w-full m-auto h-[476px] text-center">
                                <img src="{{ bagisto_asset('images/thank-you.png') }}" />
                                <p class="text-xl">
                                    @lang('shop::app.categories.view.empty')
                                </p>
                            </div>
                        </template>
                    </div>

                    <!-- Loading Spinner -->
                    <div class="w-full flex justify-center py-4" v-if="isLoadingMore">
                        <div class="loader border-t-2 border-b-2 border-gray-500 rounded-full w-10 h-10 animate-spin"></div>
                    </div>

                    <div ref="loadMoreObserver"></div>
                </div>
            </div>
        </div>
    </script>

        <script type="module">
            app.component('v-search', {
                template: '#v-search-template',

                data() {
                    return {
                        isMobile: window.innerWidth <= 767,
                        isLoading: true,
                        isLoadingMore: false,
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
                    };
                },

                computed: {
                    queryParams() {
                        let queryParams = Object.assign({}, this.filters.filter, this.filters.toolbar);
                        return this.removeJsonEmptyValues(queryParams);
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
                    this.setupIntersectionObserver();
                },

                methods: {
                    setupIntersectionObserver() {
                        const observer = new IntersectionObserver((entries) => {
                            if (entries[0].isIntersecting && this.links.next) {
                                this.loadMoreProducts();
                            }
                        });

                        observer.observe(this.$refs.loadMoreObserver);
                    },

                    setFilters(type, filters) {
                        this.filters[type] = filters;
                    },

                    clearFilters(type) {
                        this.filters[type] = {};
                    },

                    getProducts() {
                        this.isDrawerActive = {
                            toolbar: false,
                            filter: false
                        };
                        this.isLoading = true;

                        this.$axios.get("{{ route('shop.api.products.index') }}", {
                                params: this.queryParams
                            })
                            .then(response => {
                                this.isLoading = false;
                                this.products = response.data.data;
                                this.links = response.data.links;
                            })
                            .catch(error => console.log(error));
                    },

                    loadMoreProducts() {
                        if (this.links.next) {
                            this.isLoadingMore = true;

                            this.$axios.get(this.links.next)
                                .then(response => {
                                    this.products = [...this.products, ...response.data.data];
                                    this.links = response.data.links;
                                    this.isLoadingMore = false;
                                })
                                .catch(error => {
                                    console.log(error);
                                    this.isLoadingMore = false;
                                });
                        }
                    },

                    removeJsonEmptyValues(params) {
                        Object.keys(params).forEach((key) => {
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
