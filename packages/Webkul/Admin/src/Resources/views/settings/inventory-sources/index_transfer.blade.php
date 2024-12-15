<x-admin::layouts>
    <!-- Title of the page -->
    <x-slot:title>
        @lang('admin::app.settings.inventory-sources.transfer.transfer-title')
    </x-slot>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="text-xl text-gray-800 dark:text-white font-bold">
            @lang('admin::app.settings.inventory-sources.transfer.transfer-title')
        </p>

        <div class="flex gap-x-2.5 items-center">

            <!-- Export Modal -->
            <x-admin::datagrid.export src="{{ route('admin.settings.inventory_sources.index_transfer') }}" />

            <a href="{{ route('admin.settings.inventory_sources.transfer') }}">
                <div class="primary-button">
                    @lang('admin::app.settings.inventory-sources.transfer.save-btn')
                </div>
            </a>


        </div>
    </div>

    {!! view_render_event('bagisto.admin.settings.inventory_sources.index_transfer.before') !!}

    <x-admin::datagrid :src="route('admin.settings.inventory_sources.index_transfer')" />

    {!! view_render_event('bagisto.admin.settings.inventory_sources.index_transfer.after') !!}

</x-admin::layouts>
