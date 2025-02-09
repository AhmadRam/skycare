<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.reporting.sales.index.brands-sales-details')
    </x-slot>

    <div class="flex  gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="py-3 text-xl text-gray-800 dark:text-white font-bold">
            @lang('admin::app.reporting.sales.index.brands-sales-details')
        </p>
    </div>

    <div class="flex gap-4">

        <a href="{{ route('admin.reporting.brands_sales_report.view', ['id' => $id, 'customer_group_id' => 0]) }}"
            class="text-sm text-blue-600 cursor-pointer transition-all hover:underline">
            All
        </a>

        <a href="{{ route('admin.reporting.brands_sales_report.view', ['id' => $id, 'customer_group_id' => 2]) }}"
            class="text-sm text-blue-600 cursor-pointer transition-all hover:underline">
            @lang('admin::app.settings.roles.edit.general')
        </a>

        <a href="{{ route('admin.reporting.brands_sales_report.view', ['id' => $id, 'customer_group_id' => 3]) }}"
            class="text-sm text-blue-600 cursor-pointer transition-all hover:underline">
            @lang('admin::app.reporting.products.sales.wholesale')
        </a>

    </div>

    <x-admin::datagrid :src="route('admin.reporting.brands_sales_report.view', ['id' => $id, 'customer_group_id' => $customer_group_id])" :isMultiRow="false">

    </x-admin::datagrid>
</x-admin::layouts>
