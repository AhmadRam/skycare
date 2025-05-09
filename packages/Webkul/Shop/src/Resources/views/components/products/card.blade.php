<v-product-card {{ $attributes }} :product="product">
</v-product-card>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-product-card-template"
    >
        <!-- Grid Card -->
        <div
            class="border-2 p-2 1180:transtion-all group w-full rounded-md 1180:relative 1180:grid 1180:content-start 1180:overflow-hidden 1180:duration-300 1180:hover:shadow-[0_5px_10px_rgba(0,0,0,0.1)]"
            v-if="mode != 'list'"
        >
            <div class="relative overflow-hidden group max-w-[291px] max-h-[300px] rounded">

                {!! view_render_event('bagisto.shop.components.products.card.image.before') !!}

                <a
                    :href="`{{ route('shop.product_or_category.index', '') }}/${product.url_key}`"
                    :aria-label="product.name + ' '"
                >
                    <x-shop::media.images.lazy
                        class="relative after:content-[' '] after:block after:pb-[calc(100%+9px)] bg-[#F5F5F5] group-hover:scale-105 transition-all duration-300"
                        ::src="product.base_image.medium_image_url"
                        ::key="product.id"
                        ::index="product.id"
                        width="291"
                        height="300"
                        ::alt="product.name"
                    />
                </a>

                {!! view_render_event('bagisto.shop.components.products.card.image.after') !!}

                <div class="action-items bg-black">

                    <p
                        class="inline-block absolute top-2.5 ltr:left-3 rtl:right-3 px-2.5  bg-[#E51A1A] rounded-[44px] text-white text-sm"
                        v-if="product.on_sale && product.is_saleable"
                    >
                        @lang('shop::app.components.products.card.sale')
                    </p>

                    <p
                        class="inline-block absolute top-2.5 ltr:left-3 rtl:right-3 px-2.5 bg-navyBlue rounded-[44px] text-white text-sm"
                        v-else-if="product.is_new && product.is_saleable"
                    >
                        @lang('shop::app.components.products.card.new')
                    </p>

                    <p
                        class="inline-block absolute top-2.5 ltr:right-3 rtl:left-3 px-2.5 bg-[#FEA82B] rounded-[44px] text-white text-sm"
                        v-if="product.is_top_sell && product.is_saleable"
                    >
                        @lang('shop::app.components.products.card.top_sell')
                    </p>

                    <p
                        class="inline-block absolute top-2.5 ltr:right-3 rtl:left-3 px-2.5 bg-[#E51A1A] rounded-[44px] text-white text-sm"
                        v-if="!product.is_saleable"
                    >
                        @lang('shop::app.components.products.card.out-of-stock')
                    </p>

                    {{-- <img loading="lazy" width="75" height="75" decoding="async" data-nimg="1" src="{{ asset('themes/shop/default/build/assets/ya-hala.webp') }}" style="color: transparent; position: absolute; bottom: 0px; right: 5px; z-index: 2;"> --}}

                    <div class="group-hover:bottom-0 opacity-0 group-hover:opacity-100 transition-all duration-300">

                        {!! view_render_event('bagisto.shop.components.products.card.wishlist_option.before') !!}

                        @if (core()->getConfigData('general.content.shop.wishlist_option'))
                            <span
                                class="flex justify-center items-center absolute top-5 ltr:right-5 rtl:left-5 w-[30px] h-[30px] bg-white rounded-md cursor-pointer text-2xl"
                                role="button"
                                aria-label="@lang('shop::app.components.products.card.add-to-wishlist')"
                                tabindex="0"
                                :class="product.is_wishlist ? 'icon-heart-fill' : 'icon-heart'"
                                @click="addToWishlist()"
                            >
                            </span>
                        @endif

                        {!! view_render_event('bagisto.shop.components.products.card.wishlist_option.after') !!}

                        {!! view_render_event('bagisto.shop.components.products.card.compare_option.before') !!}

                        @if (core()->getConfigData('general.content.shop.compare_option'))
                            <span
                                class="icon-compare flex justify-center items-center w-[30px] h-[30px] absolute top-16 ltr:right-5 rtl:left-5 bg-white rounded-md cursor-pointer text-2xl"
                                role="button"
                                aria-label="@lang('shop::app.components.products.card.add-to-compare')"
                                tabindex="0"
                                @click="addToCompare(product.id)"
                            >
                            </span>
                        @endif

                        {!! view_render_event('bagisto.shop.components.products.card.compare_option.after') !!}

                        {!! view_render_event('bagisto.shop.components.products.card.add_to_cart.before') !!}

                        {{-- <button
                            class="absolute bottom-4 left-1/2 py-3 px-11 bg-white rounded-xl text-navyBlue text-xs w-max font-medium cursor-pointer -translate-x-1/2 translate-y-[54px] group-hover:translate-y-0 transition-all duration-300"
                            :disabled="! product.is_saleable"
                            @click="addToCart()"
                            ref="addToCartButton"
                        >
                            @lang('shop::app.components.products.card.add-to-cart')
                        </button> --}}

                        {!! view_render_event('bagisto.shop.components.products.card.add_to_cart.after') !!}
                    </div>
                </div>
            </div>

            <div class="grid gap-2.5 content-start max-w-[291px]">

                {!! view_render_event('bagisto.shop.components.products.card.name.before') !!}

                <a :href="`{{ route('shop.product_or_category.index', '') }}/${product.url_key}`":aria-label="product.name + ' '"><p class="text-base" v-text="product.name"></p></a>

                {!! view_render_event('bagisto.shop.components.products.card.name.after') !!}

                {!! view_render_event('bagisto.shop.components.products.card.price.before') !!}

                <div
                    class="flex gap-2.5 items-center font-semibold text-lg"
                    v-html="product.price_html"
                >
                </div>

                <button style="margin-inline-start: auto;margin-inline-end: auto;"
                    class="relative py-3 px-11 bg-gray-200 rounded-xl text-navyBlue text-xs font-medium"
                    :disabled="! product.is_saleable"
                    @click="addToCart()"
                    ref="addToCartButton"
                >
                <i class="icon-heart-fill"></i>

                @lang('shop::app.components.products.card.add-to-cart')
            </button>

                {!! view_render_event('bagisto.shop.components.products.card.price.before') !!}

                <!-- Needs to implement that in future -->
                <div class="hidden flex gap-4 mt-2">
                    <span class="block w-[30px] h-[30px] bg-[#B5DCB4] rounded-full cursor-pointer"></span>

                    <span class="block w-[30px] h-[30px] bg-[#5C5C5C] rounded-full cursor-pointer"></span>
                </div>
            </div>
        </div>

        <!-- List Card -->
        <div
            class="flex gap-4 grid-cols-2 max-w-max relative max-sm:flex-wrap rounded overflow-hidden"
            v-else
        >
            <div class="relative max-w-[250px] max-h-[258px] overflow-hidden group">

                {!! view_render_event('bagisto.shop.components.products.card.image.before') !!}

                <a :href="`{{ route('shop.product_or_category.index', '') }}/${product.url_key}`">
                    <x-shop::media.images.lazy
                        class="min-w-[250px] relative after:content-[' '] after:block after:pb-[calc(100%+9px)] bg-[#F5F5F5] group-hover:scale-105 transition-all duration-300"
                        ::src="product.base_image.medium_image_url"
                        ::key="product.id"
                        ::index="product.id"
                        width="291"
                        height="300"
                        ::alt="product.name"
                    />
                </a>

                {!! view_render_event('bagisto.shop.components.products.card.image.after') !!}

                <div class="action-items bg-black">
                    <p
                        class="inline-block absolute top-2.5 ltr:left-3 rtl:right-3 px-2.5 bg-[#E51A1A] rounded-[44px] text-white text-sm"
                        v-if="product.on_sale"
                    >
                        @lang('shop::app.components.products.card.sale')
                    </p>

                    <p
                        class="inline-block absolute top-2.5 ltr:left-3 rtl:right-3 px-2.5 bg-navyBlue rounded-[44px] text-white text-sm"
                        v-else-if="product.is_new"
                    >
                        @lang('shop::app.components.products.card.new')
                    </p>

                    <p
                    class="inline-block absolute top-2.5 ltr:right-3 rtl:left-3 px-2.5 bg-[#FEA82B] rounded-[44px] text-white text-sm"
                    v-if="product.is_top_sell"
                    >
                        @lang('shop::app.components.products.card.top_sell')
                    </p>

                    <div class="group-hover:bottom-0 opacity-0 group-hover:opacity-100 transition-all duration-300">

                        {!! view_render_event('bagisto.shop.components.products.card.wishlist_option.before') !!}

                        @if (core()->getConfigData('general.content.shop.wishlist_option'))
                            <span
                                class="flex justify-center items-center absolute top-5 ltr:right-5 rtl:left-5 w-[30px] h-[30px] bg-white rounded-md text-2xl cursor-pointer"
                                role="button"
                                aria-label="@lang('shop::app.components.products.card.add-to-wishlist')"
                                tabindex="0"
                                :class="product.is_wishlist ? 'icon-heart-fill' : 'icon-heart'"
                                @click="addToWishlist()"
                            >
                            </span>
                        @endif

                        {!! view_render_event('bagisto.shop.components.products.card.wishlist_option.after') !!}

                        {!! view_render_event('bagisto.shop.components.products.card.compare_option.before') !!}

                        @if (core()->getConfigData('general.content.shop.compare_option'))
                            <span
                                class="icon-compare flex justify-center items-center absolute top-16 ltr:right-5 rtl:left-5 w-[30px] h-[30px] bg-white rounded-md text-2xl cursor-pointer"
                                role="button"
                                aria-label="@lang('shop::app.components.products.card.add-to-compare')"
                                tabindex="0"
                                @click="addToCompare(product.id)"
                            >
                            </span>
                        @endif

                        {!! view_render_event('bagisto.shop.components.products.card.compare_option.after') !!}
                    </div>
                </div>
            </div>

            <div class="grid gap-4 content-start">

                {!! view_render_event('bagisto.shop.components.products.card.name.before') !!}

                <p
                    class="text-base"
                    v-text="product.name"
                >
                </p>

                {!! view_render_event('bagisto.shop.components.products.card.name.after') !!}

                {!! view_render_event('bagisto.shop.components.products.card.price.before') !!}

                <div
                    class="flex gap-2.5 text-lg font-semibold"
                    v-html="product.price_html"
                >
                </div>

                {!! view_render_event('bagisto.shop.components.products.card.price.after') !!}

                <!-- Needs to implement that in future -->
                <div class="hidden flex gap-4">
                    <span class="block w-[30px] h-[30px] rounded-full bg-[#B5DCB4]">
                    </span>

                    <span class="block w-[30px] h-[30px] rounded-full bg-[#5C5C5C]">
                    </span>
                </div>

                {!! view_render_event('bagisto.shop.components.products.card.price.after') !!}

                <p class="text-sm text-[#6E6E6E]" v-if="! product.avg_ratings">
                    @lang('shop::app.components.products.card.review-description')
                </p>

                {!! view_render_event('bagisto.shop.components.products.card.average_ratings.before') !!}

                <p v-else class="text-sm text-[#6E6E6E]">
                    <x-shop::products.star-rating
                        ::value="product && product.avg_ratings ? product.avg_ratings : 0"
                        :is-editable=false
                    />
                </p>

                {!! view_render_event('bagisto.shop.components.products.card.average_ratings.after') !!}

                {!! view_render_event('bagisto.shop.components.products.card.add_to_cart.before') !!}

                <x-shop::button
                    class="primary-button px-8 py-2.5 whitespace-nowrap"
                    :title="trans('shop::app.components.products.card.add-to-cart')"
                    :loading="false"
                    ::disabled="! product.is_saleable"
                    ref="addToCartButton"
                    @click="addToCart()"
                />

                {!! view_render_event('bagisto.shop.components.products.card.add_to_cart.after') !!}
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-product-card', {
            template: '#v-product-card-template',

            props: ['mode', 'product'],

            data() {
                return {
                    isCustomer: '{{ auth()->guard('customer')->check() }}',
                }
            },

            methods: {
                addToWishlist() {
                    if (this.isCustomer) {
                        this.$axios.post(`{{ route('shop.api.customers.account.wishlist.store') }}`, {
                                product_id: this.product.id
                            })
                            .then(response => {
                                this.product.is_wishlist = !this.product.is_wishlist;

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.data.message
                                });
                            })
                            .catch(error => {});
                    } else {
                        window.location.href = "{{ route('shop.customer.session.index') }}";
                    }
                },

                addToCompare(productId) {
                    /**
                     * This will handle for customers.
                     */
                    if (this.isCustomer) {
                        this.$axios.post('{{ route('shop.api.compare.store') }}', {
                                'product_id': productId
                            })
                            .then(response => {
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.data.message
                                });
                            })
                            .catch(error => {
                                if ([400, 422].includes(error.response.status)) {
                                    this.$emitter.emit('add-flash', {
                                        type: 'warning',
                                        message: error.response.data.data.message
                                    });

                                    return;
                                }

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response.data.message
                                });
                            });

                        return;
                    }

                    /**
                     * This will handle for guests.
                     */
                    let items = this.getStorageValue() ?? [];

                    if (items.length) {
                        if (!items.includes(productId)) {
                            items.push(productId);

                            localStorage.setItem('compare_items', JSON.stringify(items));

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: "@lang('shop::app.components.products.card.add-to-compare-success')"
                            });
                        } else {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: "@lang('shop::app.components.products.card.already-in-compare')"
                            });
                        }
                    } else {
                        localStorage.setItem('compare_items', JSON.stringify([productId]));

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: "@lang('shop::app.components.products.card.add-to-compare-success')"
                        });

                    }
                },

                getStorageValue(key) {
                    let value = localStorage.getItem('compare_items');

                    if (!value) {
                        return [];
                    }

                    return JSON.parse(value);
                },

                addToCart() {

                    this.$refs.addToCartButton.isLoading = true;

                    this.$axios.post('{{ route('shop.api.checkout.cart.store') }}', {
                            'quantity': 1,
                            'product_id': this.product.id,
                        })
                        .then(response => {
                            if (response.data.data.redirect_uri) {
                                window.location.href = response.data.data.redirect_uri;
                            }

                            if (response.data.message) {
                                this.$emitter.emit('update-mini-cart', response.data.data);

                                fbq('track', 'AddToCart', {
                                    content_name: this.product.name,
                                    content_ids: [this.product.id],
                                    content_type: 'product',
                                    value: this.product.price,
                                    currency: '{{ core()->getCurrentCurrency()->code }}'
                                });


                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message
                                });

                                setTimeout(function() {
                                    window.eventBus.$emit('toggle-open');
                                }, 500);

                            } else {
                                this.$emitter.emit('add-flash', {
                                    type: 'warning',
                                    message: response.data.data.message
                                });
                            }

                            this.$refs.addToCartButton.isLoading = false;
                        })
                        .catch(error => {
                            this.$refs.addToCartButton.isLoading = false;

                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: response.data.message
                            });
                        });
                },
            },
        });
    </script>
@endpushOnce
