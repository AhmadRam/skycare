<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ $cart_rulte->code }}
    </x-slot>

    <!-- Page Header -->
    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="py-3 text-xl text-gray-800 dark:text-white font-bold">
            {{ $cart_rulte->code }}
        </p>
    </div>

    <!-- Results Table -->
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white dark:bg-gray-800 border rounded-lg shadow-md">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700">
                    <th class="text-left px-4 py-2 text-gray-600 dark:text-gray-300">@lang('admin::app.catalog.brands.index.title')</th>
                    <th class="text-left px-4 py-2 text-gray-600 dark:text-gray-300">@lang('admin::app.sales.orders.view.quantity')</th>
                    <th class="text-left px-4 py-2 text-gray-600 dark:text-gray-300">@lang('admin::app.reporting.sales.index.total')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orderItems as $item)
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-200">
                            {{ $item->attribute_value_label }}
                        </td>
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-200">
                            {{ $item->total_quantity }}
                        </td>
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-200">
                            {{ core()->currency($item->base_total) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center px-4 py-4 text-gray-500 dark:text-gray-400">
                            @lang('admin::app.reporting.sales.coupon-codes-details.no-data')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin::layouts>
