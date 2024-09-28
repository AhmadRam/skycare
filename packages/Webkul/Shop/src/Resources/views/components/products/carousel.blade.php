<v-products-carousel src="{{ $src }}" title="{{ $title }}" navigation-link="{{ $navigationLink ?? '' }}">
    <x-shop::shimmer.products.carousel :navigation-link="$navigationLink ?? false" />
</v-products-carousel>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-products-carousel-template"
    >
        <div
            class="container mt-20 max-lg:px-8 max-md:mt-8 max-sm:mt-7 max-sm:!px-4"
            v-if="! isLoading && products.length"
        >
            <div class="flex justify-between">
                <h2 class="font-dmserif text-3xl max-md:text-2xl max-sm:text-xl">
                    @{{ title }}
                </h2>

                <div class="flex items-center justify-between gap-8">
                    <a
                        :href="navigationLink"
                        class="hidden max-lg:flex"
                        v-if="navigationLink"
                    >
                        <p class="items-center text-xl max-md:text-base max-sm:text-sm">
                            @lang('shop::app.components.products.carousel.view-all')

                            <span class="icon-arrow-right text-2xl max-md:text-lg max-sm:text-sm"></span>
                        </p>
                    </a>

                    <span
                        class="icon-arrow-left-stylish rtl:icon-arrow-right-stylish inline-block cursor-pointer text-2xl max-lg:hidden"
                        role="button"
                        aria-label="@lang('shop::app.components.products.carousel.previous')"
                        tabindex="0"
                        @click="swipeLeft"
                    >
                    </span>

                    <span
                        class="icon-arrow-right-stylish rtl:icon-arrow-left-stylish inline-block cursor-pointer text-2xl max-lg:hidden"
                        role="button"
                        aria-label="@lang('shop::app.components.products.carousel.next')"
                        tabindex="0"
                        @click="swipeRight"
                    >
                    </span>
                </div>
            </div>

            <div
                ref="swiperContainer"
                class="flex gap-8 pb-2.5 [&>*]:flex-[0] mt-10 overflow-auto scroll-smooth scrollbar-hide max-md:gap-7 max-md:mt-5 max-sm:gap-4 max-md:pb-0 max-md:whitespace-nowrap"
            >
                <x-shop::products.card
                    class="min-w-[291px] max-md:h-fit max-md:min-w-56 max-sm:min-w-[192px]"
                    v-for="product in clonedProducts"
                />
            </div>

            <a
                :href="navigationLink"
                class="secondary-button mx-auto mt-5 block w-max rounded-2xl px-11 py-3 text-center text-base max-lg:mt-0 max-lg:hidden max-lg:py-3.5 max-md:rounded-lg"
                :aria-label="title"
                v-if="navigationLink"
            >
                @lang('shop::app.components.products.carousel.view-all')
            </a>
        </div>

        <!-- Product Card Listing -->
        <template v-if="isLoading">
            <x-shop::shimmer.products.carousel :navigation-link="$navigationLink ?? false" />
        </template>
    </script>

    <script type="module">
        app.component('v-products-carousel', {
            template: '#v-products-carousel-template',

            props: [
                'src',
                'title',
                'navigationLink',
            ],

            data() {
                return {
                    isLoading: true,
                    products: [],
                    offset: 323,
                    autoScrollInterval: null,
                    scrollDelay: 3000, // Delay in ms between auto-scrolls
                    clonedProducts: [], // Array to hold cloned products for infinite scrolling
                };
            },

            mounted() {
                this.getProducts();
            },

            beforeUnmount() {
                this.stopAutoScroll(); // Clean up interval when component is destroyed
            },

            methods: {
                getProducts() {
                    this.$axios.get(this.src)
                        .then(response => {
                            this.isLoading = false;
                            this.products = response.data.data;

                            // Clone products to the start and end to enable infinite scroll
                            this.clonedProducts = [
                                ...this.products, // Original list of products
                                ...this.products, // Clone the list at the end
                            ];

                            this.$nextTick(() => {
                                this.startAutoScroll(); // Start auto-scroll after the DOM is updated
                            });
                        })
                        .catch(error => {
                            console.log(error);
                        });
                },

                swipeLeft() {
                    const container = this.$refs.swiperContainer;

                    if (container.scrollLeft === 0) {
                        // If we are at the very beginning, move to the cloned end (seamless transition)
                        container.scrollLeft = container.scrollWidth / 2;
                    } else {
                        container.scrollLeft -= this.offset;
                    }
                },

                swipeRight() {
                    const container = this.$refs.swiperContainer;

                    // Check if we are at the end of the list (including cloned items)
                    if (container.scrollLeft + container.clientWidth >= container.scrollWidth) {
                        // Jump back to the original start (seamless transition)
                        container.scrollLeft = container.scrollWidth / 2 - container.clientWidth;
                    } else {
                        container.scrollLeft += this.offset;
                    }
                },

                startAutoScroll() {
                    // Start auto-scrolling every `scrollDelay` milliseconds
                    var locale = `{{ app()->getLocale() }}`;
                    if (locale == 'en') {
                        this.autoScrollInterval = setInterval(this.swipeLeft, this.scrollDelay);
                    } else {
                        this.autoScrollInterval = setInterval(this.swipeRight, this.scrollDelay);
                    }
                },

                stopAutoScroll() {
                    // Clear the auto-scroll interval to stop scrolling
                    clearInterval(this.autoScrollInterval);
                },
            },
        });
    </script>
@endPushOnce
