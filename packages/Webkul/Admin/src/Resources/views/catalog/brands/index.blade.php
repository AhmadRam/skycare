<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.brands.index.title')
    </x-slot>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="text-xl text-gray-800 dark:text-white font-bold">
            @lang('admin::app.catalog.brands.index.title')
        </p>

        <div class="flex gap-x-2.5 items-center">

            {!! view_render_event('bagisto.admin.catalog.brands.index.create-button.before') !!}

                <a href="{{ route('admin.catalog.brands.create') }}">
                    <div class="primary-button">
                        @lang('admin::app.catalog.brands.index.add-btn')
                    </div>
                </a>

            {!! view_render_event('bagisto.admin.catalog.brands.index.create-button.after') !!}
        </div>
    </div>

    {!! view_render_event('bagisto.admin.catalog.brands.list.before') !!}

    <x-admin::datagrid src="{{ route('admin.catalog.brands.index') }}" />

    {!! view_render_event('bagisto.admin.catalog.brands.list.after') !!}

</x-admin::layouts>
