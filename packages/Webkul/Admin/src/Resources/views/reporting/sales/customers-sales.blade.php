<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        Customer Sales
    </x-slot>

    <div class="flex  gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="py-3 text-xl text-gray-800 dark:text-white font-bold">
            Customer Sales
        </p>

        <div class="flex gap-x-2.5 items-center">
            <!-- Export Modal -->
            <x-admin::datagrid.export
                src="{{ route('admin.reporting.customers_sales.index', ['customer_group' => request()->customer_group]) }}" />
        </div>

    </div>

    <div class="flex gap-4">

        <a href="{{ route('admin.reporting.customers_sales.index', ['customer_group_id' => 0]) }}"
            class="text-sm text-blue-600 cursor-pointer transition-all hover:underline">
            All
        </a>

        <a href="{{ route('admin.reporting.customers_sales.index', ['customer_group_id' => 2]) }}"
            class="text-sm text-blue-600 cursor-pointer transition-all hover:underline">
            @lang('admin::app.settings.roles.edit.general')
        </a>

        <a href="{{ route('admin.reporting.customers_sales.index', ['customer_group_id' => 3]) }}"
            class="text-sm text-blue-600 cursor-pointer transition-all hover:underline">
            @lang('admin::app.reporting.products.sales.wholesale')
        </a>

    </div>

    <x-admin::datagrid :src="route('admin.reporting.customers_sales.index', [
        'customer_group_id' => request()->customer_group_id ?? null,
    ])" :isMultiRow="false">

    </x-admin::datagrid>
</x-admin::layouts>
