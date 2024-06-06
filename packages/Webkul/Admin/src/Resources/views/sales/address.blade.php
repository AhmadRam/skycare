<div class="flex flex-col">
    <p class="text-gray-800 font-semibold leading-6 dark:text-white">
        {{ $address->company_name ?? '' }}
    </p>

    <p class="text-gray-800 font-semibold leading-6 dark:text-white">
        {{ $address->name }}
    </p>

    <p class="text-gray-600 dark:text-gray-300 !leading-6">
        @if ($address->country)
            <?php $address1 = explode(PHP_EOL, $address->address1); ?>
            @if (isset($address1[0]))
                {{ trans('shop::app.checkout.onepage.addresses.billing.block-address') }} :
                {{ $address1[0] ?? null }}<br>
            @endif
            @if (isset($address1[1]))
                {{ trans('shop::app.checkout.onepage.addresses.billing.street-address') }} :
                {{ $address1[1] ?? null }}<br>
            @endif
            @if (isset($address1[3]))
                {{ trans('shop::app.checkout.onepage.addresses.billing.house-address') }} :
                {{ (isset($address1[2]) ? 'House No:' . $address1[2] : '') . (isset($address1[3]) ? ' / Floor: ' . $address1[3] : '') . (isset($address1[4]) ? ' / Flat: ' . $address1[4] : '') }}<br>
            @endif
            @if (isset($address1[5]))
                {{ trans('shop::app.checkout.onepage.addresses.billing.avenue-address') }} :
                {{ $address1[5] ?? null }}<br>
            @endif
        @else
            {{ $address->address1 }}<br>
        @endif

        {{ trans('shop::app.checkout.onepage.addresses.billing.note-address') }} : {{ $address->note }}<br>

        @if ($address->address2)
            {{ $address->address2 }}<br>
        @endif

        {{ $address->state }}<br>

        {{ $address->city }}<br>

        {{ core()->country_name($address->country) }}
        @if ($address->postcode)
            ({{ $address->postcode }})
        @endif
        <br>

        {{ __('admin::app.sales.orders.view.contact') }} : {{ $address->phone }}
    </p>
</div>



<?php $address1 = explode(PHP_EOL, $address->address1); ?>
<p>{{ $address->company_name ?? '' }}</p>
{{-- <p>{{ $address->name }}</p> --}}
<p>{{ core()->country_name($address->country) }}</p>
<p>{{ $address->state }}</p>
<p>{{ $address->postcode }} {{ $address->city }}</p>
<p>{{ trans('admin::app.customers.addresses.block') }} : {{ $address1[0] ?? null }}</p>
<p>{{ trans('admin::app.customers.addresses.street') }} : {{ $address1[1] ?? null }}</p>
<p>{{ trans('admin::app.customers.addresses.house') }} :
    {{ (isset($address1[2]) ? 'House No:' . $address1[2] : '') . (isset($address1[3]) ? ' / Floor: ' . $address1[3] : '') . (isset($address1[4]) ? ' / Flat: ' . $address1[4] : '') }}
</p>
<p>{{ trans('admin::app.customers.addresses.avenue') }} : {{ $address1[5] ?? null }}</p>
<p>{{ trans('admin::app.customers.addresses.note') }} : {{ $address->note }}</p>
<p><i class="fas fa-phone-square-alt"></i> <a
        href="tel:{{ $address->phone_code }}{{ $address->phone }}">{{ $address->phone_code }}{{ $address->phone }}</a>
</p>
