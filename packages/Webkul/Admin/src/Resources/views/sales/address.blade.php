<div class="flex flex-col">
    <p class="text-gray-800 font-semibold leading-6 dark:text-white">
        {{ $address->company_name ?? '' }}
    </p>

    <p class="text-gray-800 font-semibold leading-6 dark:text-white">
        {{ $address->name }}
    </p>

    <p class="text-gray-600 dark:text-gray-300 !leading-6">
        <?php $address1 = explode(PHP_EOL, $address->address1); ?>

        {{ trans('shop::app.checkout.onepage.addresses.billing.block-address') }} : {{ $address1[0] }}<br>
        {{ trans('shop::app.checkout.onepage.addresses.billing.street-address') }} : {{ $address1[1] }}<br>
        {{ trans('shop::app.checkout.onepage.addresses.billing.floor-address') }} : {{ $address1[2] }}<br>
        {{ trans('shop::app.checkout.onepage.addresses.billing.house-address') }} : {{ $address1[3] }}<br>

        @if ($address->address2)
            {{ $address->address2 }}<br>
        @endif

        {{ $address->city }}<br>

        {{ $address->state }}<br>

        {{ core()->country_name($address->country) }} @if ($address->postcode)
            ({{ $address->postcode }})
        @endif
        <br>

        {{ __('admin::app.sales.orders.view.contact') }} : {{ $address->phone }}
    </p>
</div>
