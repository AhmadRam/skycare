<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.reporting.sales.index.brands-sales')
    </x-slot>

    <div class="flex  gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="py-3 text-xl text-gray-800 dark:text-white font-bold">
            @lang('admin::app.reporting.sales.index.brands-sales')
        </p>
    </div>

    <x-admin::datagrid :src="route('admin.reporting.brands_sales_report.index')" :isMultiRow="false">

    </x-admin::datagrid>
</x-admin::layouts>
