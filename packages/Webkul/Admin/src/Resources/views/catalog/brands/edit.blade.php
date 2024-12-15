<x-admin::layouts>
    <!-- Title of the page -->
    <x-slot:title>
        @lang('admin::app.catalog.brands.create.edit-title')
    </x-slot>

    @php
        $currentLocale = core()->getRequestedLocale();
    @endphp

    {!! view_render_event('bagisto.admin.catalog.categories.edit.before') !!}

    <!-- Brand Edit Form -->
    <x-admin::form :action="route('admin.catalog.brands.update', $brand->id)" enctype="multipart/form-data" method="PUT">

        {!! view_render_event('bagisto.admin.catalog.categories.edit.edit_form_controls.before', ['brand' => $brand]) !!}

        <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
            <p class="text-xl text-gray-800 dark:text-white font-bold">
                @lang('admin::app.catalog.brands.create.edit-title')
            </p>

            <div class="flex gap-x-2.5 items-center">
                <!-- Back Button -->
                <a href="{{ route('admin.catalog.categories.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white">
                    @lang('admin::app.catalog.categories.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button type="submit" class="primary-button">
                    @lang('admin::app.catalog.categories.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- Filter Row -->
        <div class="flex  gap-4 justify-between items-center mt-7 max-md:flex-wrap">
            <div class="flex gap-x-1 items-center">
                <!-- Locale Switcher -->

                <x-admin::dropdown :class="core()->getAllLocales()->count() <= 1 ? 'hidden' : ''">
                    <!-- Dropdown Toggler -->
                    <x-slot:toggle>
                        <button type="button"
                            class="transparent-button px-1 py-1.5 hover:bg-gray-200 dark:hover:bg-gray-800 focus:bg-gray-200 dark:focus:bg-gray-800 dark:text-white">
                            <span class="icon-language text-2xl"></span>

                            {{ $currentLocale->name }}

                            <input type="hidden" name="locale" value="{{ $currentLocale->code }}" />

                            <span class="icon-sort-down text-2xl"></span>
                        </button>
                    </x-slot>

                    <!-- Dropdown Content -->
                    <x-slot:content class="!p-0">
                        @foreach (core()->getAllLocales() as $locale)
                            <a href="?{{ Arr::query(['locale' => $locale->code]) }}"
                                class="flex gap-2.5 px-5 py-2 text-base cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : '' }}">
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>
            </div>
        </div>

        <!-- Full Pannel -->
        <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">
            <!-- Left Section -->
            <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">

                {!! view_render_event('bagisto.admin.catalog.categories.edit.card.general.before', ['brand' => $brand]) !!}

                <!-- General -->
                <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
                    <p class="mb-4 text-base text-gray-800 dark:text-white font-semibold">
                        @lang('admin::app.catalog.categories.edit.general')
                    </p>

                    <!-- admin_name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.catalog.brands.create.admin_name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control type="text" name="admin_name" rules="required"
                            :value="old('admin_name') ?: $brand->admin_name" :label="trans('admin::app.catalog.brands.create.admin_name')" :placeholder="trans('admin::app.catalog.brands.create.admin_name')" />

                        <x-admin::form.control-group.error control-name="admin_name" />
                    </x-admin::form.control-group>


                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.catalog.brands.create.name')
                        </x-admin::form.control-group.label>

                        <v-field type="text" name="{{ $currentLocale->code }}[label]"
                            value="{{ old($currentLocale->code)['label'] ?? ($brand->translate($currentLocale->code)['label'] ?? '') }}"
                            label="{{ trans('admin::app.catalog.brands.create.label') }}" rules="required"
                            v-slot="{ field }">
                            <input type="text" name="{{ $currentLocale->code }}[label]"
                                id="{{ $currentLocale->code }}[label]" v-bind="field"
                                :class="[errors['{{ $currentLocale->code }}[label]'] ?
                                    'border border-red-600 hover:border-red-600' : ''
                                ]"
                                class="flex w-full min-h-[39px] py-2 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                placeholder="{{ trans('admin::app.catalog.brands.create.name') }}"
                                v-slugify-target:{{ $currentLocale->code . '[slug]' }}="setValues">
                        </v-field>

                        <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[label]" />
                    </x-admin::form.control-group>

                </div>

                {!! view_render_event('bagisto.admin.catalog.categories.edit.card.general.after', ['brand' => $brand]) !!}

                {!! view_render_event('bagisto.admin.catalog.categories.edit.card.description_images.before', [
                    'brand' => $brand,
                ]) !!}

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

                            <x-admin::media.images name="swatch_value" :uploaded-images="url('cache/small/' . $brand->swatch_value)
                                ? [['id' => 'swatch_value', 'url' => url('cache/small/' . $brand->swatch_value)]]
                                : []" width="220px" />
                        </div>
                    </div>
                </div>

                {!! view_render_event('bagisto.admin.catalog.categories.create.card.description_images.after', [
                    'brand' => $brand,
                ]) !!}


                {!! view_render_event('bagisto.admin.catalog.categories.edit.card.seo.before', ['brand' => $brand]) !!}

                <!-- SEO Deatils -->
                <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
                    <p class=" mb-4 text-base text-gray-800 dark:text-white font-semibold">
                        @lang('admin::app.catalog.categories.edit.seo-details')
                    </p>

                    <!-- SEO Title & Description Blade Componnet -->
                    {{-- <x-admin::seo/> --}}

                    <div class="mt-8">
                        <!-- Meta Title -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.catalog.categories.edit.meta-title')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="text" id="meta_title"
                                name="{{ $currentLocale->code }}[meta_title]" :value="old($currentLocale->code)['meta_title'] ??
                                    ($brand->translate($currentLocale->code)['meta_title'] ?? '')" :label="trans('admin::app.catalog.categories.edit.meta-title')"
                                :placeholder="trans('admin::app.catalog.categories.edit.meta-title')" />

                        </x-admin::form.control-group>

                        <!-- Meta Keywords -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.catalog.categories.edit.meta-keywords')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="text"
                                name="{{ $currentLocale->code }}[meta_keywords]" :value="old($currentLocale->code)['meta_keywords'] ??
                                    ($brand->translate($currentLocale->code)['meta_keywords'] ?? '')" :label="trans('admin::app.catalog.categories.edit.meta-keywords')"
                                :placeholder="trans('admin::app.catalog.categories.edit.meta-keywords')" />
                        </x-admin::form.control-group>

                        <!-- Meta Description -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.catalog.categories.edit.meta-description')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="textarea" id="meta_description"
                                name="{{ $currentLocale->code }}[meta_description]" :value="old($currentLocale->code)['meta_description'] ??
                                    ($brand->translate($currentLocale->code)['meta_description'] ?? '')"
                                :label="trans('admin::app.catalog.categories.edit.meta-description')" :placeholder="trans('admin::app.catalog.categories.edit.meta-description')" />
                        </x-admin::form.control-group>
                    </div>
                </div>

                {!! view_render_event('bagisto.admin.catalog.categories.edit.card.seo.after', ['brand' => $brand]) !!}
            </div>

            <!-- Right Section -->
            <div class="flex flex-col gap-2 w-[360px] max-w-full">
                <!-- Settings -->

                {!! view_render_event('bagisto.admin.catalog.categories.edit.card.accordion.settings.before', [
                    'brand' => $brand,
                ]) !!}

                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base text-gray-800 dark:text-white font-semibold">
                            @lang('admin::app.catalog.categories.edit.settings')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <!-- Position -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.catalog.brands.sort_order')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control type="text" name="sort_order" rules="required"
                                :value="old('sort_order') ?: $brand->sort_order" :label="trans('admin::app.catalog.brands.sort_order')" :placeholder="trans('admin::app.catalog.brands.sort_order')" />

                            <x-admin::form.control-group.error control-name="sort_order" />
                        </x-admin::form.control-group>

                        <!-- Visible in menu -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.catalog.categories.edit.visible-in-menu')
                            </x-admin::form.control-group.label>

                            @php $selectedValue = old('status') ?: $brand->status @endphp

                            <!-- Visible in menu Hidden field -->
                            <x-admin::form.control-group.control type="hidden" class="cursor-pointer" name="status"
                                :checked="(bool) $selectedValue" />

                            <x-admin::form.control-group.control type="switch" class="cursor-pointer" name="status"
                                value="1" :label="trans('admin::app.catalog.categories.edit.visible-in-menu')" :checked="(bool) $selectedValue" />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                {!! view_render_event('bagisto.admin.catalog.categories.edit.card.accordion.settings.after', [
                    'brand' => $brand,
                ]) !!}

            </div>
        </div>

        {!! view_render_event('bagisto.admin.catalog.categories.edit.edit_form_controls.after', ['brand' => $brand]) !!}

    </x-admin::form>

    {!! view_render_event('bagisto.admin.catalog.categories.edit.after') !!}

</x-admin::layouts>
