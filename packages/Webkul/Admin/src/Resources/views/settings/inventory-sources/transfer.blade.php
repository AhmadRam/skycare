<x-admin::layouts>
    <!-- Title of the page -->
    <x-slot:title>
        @lang('admin::app.settings.inventory-sources.transfer.transfer-title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.inventory_sources.transfer.before') !!}

    <!-- transfer Inventory -->
    <v-inventory-transfer-form></v-inventory-transfer-form>

    {!! view_render_event('bagisto.admin.settings.inventory_sources.transfer.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-inventory-transfer-form-template"
        >
            <div>
                {!! view_render_event('bagisto.admin.settings.inventory_sources.transfer.before') !!}

                <x-admin::form
                    :action="route('admin.settings.inventory_sources.store-transfer')"
                    enctype="multipart/form-data"
                >
                    {!! view_render_event('bagisto.admin.settings.inventory_sources.transfer.transfer_form_controls.before') !!}

                    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
                        <p class="text-xl text-gray-800 dark:text-white font-bold">
                            @lang('admin::app.settings.inventory-sources.transfer.transfer-title')
                        </p>

                        <div class="flex gap-x-2.5 items-center">
                            <!-- Cancel Button -->
                            <a
                                href="{{ route('admin.settings.inventory_sources.index') }}"
                                class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                            >
                                @lang('admin::app.settings.inventory-sources.transfer.back-btn')
                            </a>

                            <!-- Save Inventory -->
                            <button
                                type="submit"
                                class="primary-button"
                            >
                                @lang('admin::app.settings.inventory-sources.transfer.save-btn')
                            </button>
                        </div>
                    </div>

                    <!-- Full Pannel -->
                    <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">
                        <!-- Left Section -->
                        <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">

                            {!! view_render_event('bagisto.admin.settings.inventory_sources.transfer.card.general.before') !!}

                            <!-- General -->
                            <div class="p-4 bg-white dark:bg-gray-900 box-shadow rounded">
                                <p class="mb-4 text-base text-gray-800 dark:text-white font-semibold">
                                    @lang('admin::app.settings.inventory-sources.transfer.general')
                                </p>

                                <div class="grid gap-2.5">
                                    <!-- Panel -->
                                    <div
                                        class="relative bg-white dark:bg-gray-900 rounded box-shadow"
                                        v-for="type in types"
                                    >
                                        <div class="flex gap-5 justify-between mb-2.5 p-4">
                                            <div class="flex flex-col gap-2">
                                                <p
                                                    class="text-base text-gray-800 dark:text-white font-semibold"
                                                    v-text="type.title"
                                                >
                                                </p>

                                                <p
                                                    class="text-xs text-gray-500 dark:text-gray-300 font-medium"
                                                    v-text="type.info"
                                                >
                                                </p>
                                            </div>

                                            <!-- Add Button -->
                                            <div class="flex gap-x-1 items-center">
                                                <div
                                                    class="secondary-button"
                                                    @click="selectedType = type.key; $refs.productSearch.openDrawer()"
                                                >
                                                    @lang('admin::app.catalog.products.edit.links.add-btn')
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Product Listing -->
                                        <div
                                            class="grid"
                                            v-if="addedProducts[type.key].length"
                                        >
                                            <div
                                                class="flex gap-2.5 justify-between p-4 border-b border-slate-300 dark:border-gray-800"
                                                v-for="product in addedProducts[type.key]"
                                            >
                                                <!-- Hidden Input -->
                                                <input
                                                    type="hidden"
                                                    :name="type.key + '[]'"
                                                    :value="product.id"
                                                />

                                                <!-- Information -->
                                                <div class="flex gap-2.5">
                                                    <!-- Image -->
                                                    <div
                                                        class="w-full h-[60px] max-w-[60px] max-h-[60px] relative rounded overflow-hidden"
                                                        :class="{'border border-dashed border-gray-300 dark:border-gray-800 dark:invert dark:mix-blend-exclusion': ! product.images.length}"
                                                    >
                                                        <template v-if="! product.images.length">
                                                            <img src="{{ bagisto_asset('images/product-placeholders/front.svg') }}">

                                                            <p class="w-full absolute bottom-1.5 text-[6px] text-gray-400 text-center font-semibold">
                                                                @lang('admin::app.catalog.products.edit.links.image-placeholder')
                                                            </p>
                                                        </template>

                                                        <template v-else>
                                                            <img :src="product.images[0].url">
                                                        </template>
                                                    </div>

                                                    <!-- Details -->
                                                    <div class="grid gap-1.5 place-content-start">
                                                        <p
                                                            class="text-base text-gray-800 dark:text-white font-semibold"
                                                            v-text="product.name"
                                                        >
                                                        </p>

                                                        <p class="text-gray-600 dark:text-gray-300">
                                                            @{{ "@lang('admin::app.catalog.products.edit.links.sku')".replace(':sku', product.sku) }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div class="grid gap-1 place-content-start text-right">
                                                    <p class="text-gray-800 font-semibold dark:text-white">
                                                        @{{ $admin.formatPrice(product.price) }}
                                                    </p>

                                                    <p
                                                        class="text-red-600 cursor-pointer transition-all hover:underline"
                                                        @click="remove(type.key, product)"
                                                    >
                                                        @lang('admin::app.catalog.products.edit.links.delete')
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- For Empty Variations -->
                                        <div
                                            class="grid gap-3.5 justify-center justify-items-center py-10 px-2.5"
                                            v-else
                                        >
                                            <!-- Placeholder Image -->
                                            <img
                                                src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                                                class="w-20 h-20 dark:invert dark:mix-blend-exclusion"
                                            />

                                            <!-- Add Variants Information -->
                                            <div class="flex flex-col gap-1.5 items-center">
                                                <p class="text-base text-gray-400 font-semibold">
                                                    @lang('admin::app.catalog.products.edit.links.empty-title')
                                                </p>

                                                <p
                                                    class="text-gray-400"
                                                    v-text="type.empty_info"
                                                >
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Product Search Blade Component -->
                                    <x-admin::products.search
                                        ref="productSearch"
                                        ::added-product-ids="addedProductIds"
                                        @onProductAdded="addSelected($event)"
                                    />
                                </div>

                                <!-- From inventory-->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.inventory-sources.transfer.from')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        id="from"
                                        name="from"
                                        rules="required"
                                        v-model="from"
                                        :label="trans('admin::app.settings.inventory-sources.transfer.from')"
                                        :placeholder="trans('admin::app.settings.inventory-sources.transfer.from')"
                                    >
                                        <option value="">
                                            @lang('admin::app.settings.inventory-sources.transfer.select-from')
                                        </option>

                                        @foreach ($inventories as $inventory)
                                            <option value="{{ $inventory->id }}">
                                                {{ $inventory->name }}
                                            </option>
                                        @endforeach
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="country" />
                                </x-admin::form.control-group>

                                <!-- To inventory-->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.inventory-sources.transfer.to')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        id="to"
                                        name="to"
                                        rules="required"
                                        v-model="to"
                                        :label="trans('admin::app.settings.inventory-sources.transfer.to')"
                                        :placeholder="trans('admin::app.settings.inventory-sources.transfer.to')"
                                    >
                                        <option value="">
                                            @lang('admin::app.settings.inventory-sources.transfer.select-to')
                                        </option>

                                        @foreach ($inventories as $inventory)
                                            <option value="{{ $inventory->id }}">
                                                {{ $inventory->name }}
                                            </option>
                                        @endforeach
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="country" />
                                </x-admin::form.control-group>

                                <!-- Qty -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.inventory-sources.transfer.qty')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="number"
                                        id="qty"
                                        name="qty"
                                        rules="required"
                                        :value="old('qty')"
                                        :label="trans('admin::app.settings.inventory-sources.transfer.qty')"
                                        :placeholder="trans('admin::app.settings.inventory-sources.transfer.qty')"
                                    />

                                    <x-admin::form.control-group.error control-name="qty" />
                                </x-admin::form.control-group>

                            </div>

                            {!! view_render_event('bagisto.admin.settings.inventory_sources.transfer.card.general.after') !!}

                        </div>

                    </div>

                    {!! view_render_event('bagisto.admin.settings.inventory_sources.transfer.transfer_form_controls.after') !!}

                </x-admin::form>

                {!! view_render_event('bagisto.admin.settings.inventory_sources.transfer.after') !!}
            </div>
        </script>

        <script type="module">
            app.component('v-inventory-transfer-form', {
                template: '#v-inventory-transfer-form-template',

                data: function() {
                    return {
                        country: "{{ old('country') }}",

                        state: "{{ old('state') }}",

                        countryStates: @json(core()->groupedStatesByCountries()),

                        selectedType: 'products',

                        types: [{
                            key: 'products',
                            title: `@lang('admin::app.catalog.products.edit.links.related-products.title')`,
                            info: `@lang('admin::app.catalog.products.edit.links.related-products.info')`,
                            empty_info: `@lang('admin::app.catalog.products.edit.links.related-products.empty-info')`,
                        }],

                        addedProducts: {
                            'products': []
                        },
                    }
                },

                methods: {
                    haveStates: function() {
                        /*
                         * The double negation operator is used to convert the value to a boolean.
                         * It ensures that the final result is a boolean value,
                         * true if the array has a length greater than 0, and otherwise false.
                         */
                        return !!this.countryStates[this.country]?.length;
                    },

                    addSelected(selectedProducts) {
                        this.addedProducts[this.selectedType] = [...[], ...[selectedProducts[0]]];
                    },

                    remove(type, product) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                this.addedProducts[type] = this.addedProducts[type].filter(item => item
                                    .id !== product.id);
                            },
                        });
                    },

                }
            })
        </script>
    @endpushOnce
</x-admin::layouts>
