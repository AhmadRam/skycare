<x-admin::layouts>
    <!-- Title of the page -->
    <x-slot:title>
        @lang('admin::app.catalog.brands.create.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.catalog.brands.create.before') !!}

    <!-- Category Create Form -->
    <x-admin::form :action="route('admin.catalog.brands.store')" enctype="multipart/form-data">
        {!! view_render_event('bagisto.admin.catalog.brands.create.create_form_controls.before') !!}

        <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
            <p class="text-xl text-gray-800 dark:text-white font-bold">
                @lang('admin::app.catalog.brands.create.title')
            </p>

            <div class="flex gap-x-2.5 items-center">
                <!-- Cancel Button -->
                <a href="{{ route('admin.catalog.brands.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white">
                    @lang('admin::app.cms.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button type="submit" class="primary-button">
                    @lang('admin::app.catalog.brands.create.save-btn')
                </button>
            </div>
        </div>

        <!-- Full Pannel -->
        <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">

            <!-- Left Section -->
            <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">

                {!! view_render_event('bagisto.admin.catalog.brands.create.card.general.before') !!}

                <!-- General -->
                <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
                    <p class="mb-4 text-base text-gray-800 dark:text-white font-semibold">
                        @lang('admin::app.catalog.brands.create.general')
                    </p>

                    <!-- Locales -->
                    <x-admin::form.control-group.control type="hidden" name="locale" value="all" />

                    <!-- admin_name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required text-gray-800 dark:text-white">
                            @lang('admin::app.catalog.brands.create.admin_name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="text" name="admin_name" rules="required"
                            :value="old('admin_name')" :label="trans('admin::app.catalog.brands.create.admin_name')" :placeholder="trans('admin::app.catalog.brands.create.admin_name')" />

                        <x-admin::form.control-group.error control-name="admin_name" />
                    </x-admin::form.control-group>

                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.catalog.brands.create.name')
                        </x-admin::form.control-group.label>

                        <v-field type="text" name="label" rules="required" value="{{ old('label') }}"
                            v-slot="{ field }" label="{{ trans('admin::app.catalog.brands.create.label') }}">
                            <input type="text" id="label"
                                :class="[errors['{{ 'label' }}'] ? 'border border-red-600 hover:border-red-600' : '']"
                                class="flex w-full min-h-[39px] py-2 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                name="label" v-bind="field"
                                placeholder="{{ trans('admin::app.catalog.brands.create.name') }}"
                                v-slugify-target:slug="setValues">
                        </v-field>

                        <x-admin::form.control-group.error control-name="label" />
                    </x-admin::form.control-group>

                </div>

                {!! view_render_event('bagisto.admin.catalog.brands.create.card.general.after') !!}

                {!! view_render_event('bagisto.admin.catalog.categories.create.card.description_images.before') !!}

                <!-- Description and images -->
                <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
                    <p class="mb-4 text-base text-gray-800 dark:text-white font-semibold">
                        @lang('admin::app.catalog.categories.create.description-and-images')
                    </p>

                    <div class="flex pt-5">

                        <!-- Add Banner -->
                        <div class="flex flex-col gap-2 w-3/5">
                            <p class="text-gray-800 dark:text-white font-medium">
                                @lang('admin::app.catalog.categories.create.banner')
                            </p>

                            <p class="text-xs text-gray-500">
                                @lang('admin::app.catalog.categories.create.banner-size')
                            </p>

                            <x-admin::media.images name="swatch_value" width="220px" />
                        </div>
                    </div>
                </div>

                {!! view_render_event('bagisto.admin.catalog.categories.create.card.description_images.after') !!}


                {!! view_render_event('bagisto.admin.catalog.brands.create.card.seo.before') !!}

                <!-- SEO Deatils -->
                <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
                    <p class="text-base text-gray-800 dark:text-white font-semibold mb-4">
                        @lang('admin::app.catalog.categories.edit.seo-details')
                    </p>

                    <!-- SEO Title & Description Blade Componnet -->
                    {{-- <x-admin::seo /> --}}

                    <div class="mt-8">
                        <!-- Meta Title -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.cms.edit.meta-title')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="text" id="meta_title" name="meta_title"
                                :value="old('meta_title')" :label="trans('admin::app.cms.edit.meta-title')" :placeholder="trans('admin::app.cms.edit.meta-title')" />
                        </x-admin::form.control-group>

                        <!-- Meta Keywords -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.cms.edit.meta-keywords')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="text" name="meta_keywords" :value="old('meta_keywords')"
                                :label="trans('admin::app.cms.edit.meta-keywords')" :placeholder="trans('admin::app.cms.edit.meta-keywords')" />
                        </x-admin::form.control-group>

                        <!-- Meta Description -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.cms.edit.meta-description')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="textarea" id="meta_description"
                                name="meta_description" :value="old('meta_description')" :label="trans('admin::app.cms.edit.meta-description')" :placeholder="trans('admin::app.cms.edit.meta-description')" />
                        </x-admin::form.control-group>
                    </div>
                </div>

                {!! view_render_event('bagisto.admin.catalog.brands.create.card.seo.after') !!}
            </div>

            <!-- Right Section -->
            <div class="flex flex-col gap-2 w-[360px] max-w-full">
                <!-- Settings -->

                {!! view_render_event('bagisto.admin.catalog.brands.create.card.accordion.settings.before') !!}

                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base text-gray-800 dark:text-white font-semibold">
                            @lang('admin::app.catalog.categories.create.settings')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <!-- Position -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required text-gray-800 dark:text-white">
                                @lang('admin::app.catalog.brands.sort_order')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="text" name="sort_order" rules="required"
                                :value="old('sort_order')" :label="trans('admin::app.catalog.brands.sort_order')" :placeholder="trans('admin::app.catalog.brands.sort_order')" />

                            <x-admin::form.control-group.error control-name="sort_order" />
                        </x-admin::form.control-group>

                        <!-- Visible in menu -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="text-gray-800 dark:text-white font-medium">
                                @lang('admin::app.catalog.categories.edit.visible-in-menu')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="switch" class="cursor-pointer" name="status"
                                value="1" :label="trans('admin::app.catalog.categories.create.visible-in-menu')" />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                {!! view_render_event('bagisto.admin.catalog.brands.create.card.accordion.settings.after') !!}

            </div>
        </div>

        {!! view_render_event('bagisto.admin.catalog.brands.create.create_form_controls.after') !!}

    </x-admin::form>

    {!! view_render_event('bagisto.admin.catalog.brands.create.after') !!}

</x-admin::layouts>
