<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>
    <!-- meta tags -->
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- lang supports inclusion -->
    <style type="text/css">
        @font-face {
            font-family: 'Hind';
            src: url({{ asset('vendor/webkul/ui/assets/fonts/Hind/Hind-Regular.ttf') }}) format('truetype');
        }

        @font-face {
            font-family: 'Noto Sans';
            src: url({{ asset('vendor/webkul/ui/assets/fonts/Noto/NotoSans-Regular.ttf') }}) format('truetype');
        }
    </style>
    <script>
        window.print();
    </script>
    @php
        /* main font will be set on locale based */
        $mainFontFamily = app()->getLocale() === 'ar' ? 'DejaVu Sans' : 'Noto Sans';
    @endphp

    <!-- main css -->
    <style type="text/css">
        * {
            font-family: '{{ $mainFontFamily }}';
        }

        body,
        th,
        td,
        h5 {
            font-size: 12px;
            color: #000;
        }

        .container {
            padding: 20px;
            display: block;
        }

        .refund-summary {
            margin-bottom: 20px;
        }

        .table {
            margin: 20px 6px 0px 6px;
            border-spacing: 0px 0px 15px 0px;
        }

        .table table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            table-layout: fixed;
        }

        .table thead th {
            font-weight: 700;
            border-top: solid 1px #d3d3d3;
            border-bottom: solid 1px #d3d3d3;
            border-left: solid 1px #d3d3d3;
            padding: 5px 12px;
            background: #005aff0d;
        }

        .table thead th:last-child {
            border-right: solid 1px #d3d3d3;
        }

        .table tbody td {
            padding: 5px 10px;
            color: #3A3A3A;
            vertical-align: middle;
            border-bottom: solid 1px #d3d3d3;
        }

        .table tbody td,
        p {
            margin: 0;
            color: #000;
        }

        .sale-summary {
            margin-top: 20px;
            float: right;
            background-color: #005aff0d;
        }

        .sale-summary tr td {
            padding: 3px 5px;
        }

        .sale-summary tr.bold {
            font-weight: 700;
        }

        .label {
            color: #000;
            font-weight: bold;
        }

        .logo {
            height: 70px;
            width: 70px;
        }

        .merchant-details {
            margin-bottom: 5px;
        }

        .merchant-details-title {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .col-6 {
            width: 42%;
            display: inline-block;
            vertical-align: top;
            margin: 0px 5px;
        }

        .table-header {
            color: #0041FF;
        }

        .align-left {
            text-align: left;
        }

        .refund-text {
            font-size: 40px;
            color: #3c41ff;
            font-weight: bold;
            position: absolute;
            width: 100%;
            left: 0;
            text-align: center;
            top: -6px;
        }

        .without_logo {
            height: 35px;
            width: 35px;
        }

        .header {
            padding: 0px 2px;
            width: 100%;
            position: relative;
            border-bottom: solid 1px #d3d3d3;
            padding-bottom: 20px;
        }
    </style>
</head>

<body style="background-image: none; background-color: #fff;">
    <div class="container">
        <div class="row">
            <div class="col-12 header" style="display: flex; justify-content: space-between; align-items: center;">
                <!-- Left Image -->
                <div class="image" style="margin-left: 20px;">
                    <img style="width: 140px; height: 140px;"
                        src="{{ asset('themes/shop/default/build/assets/skycare_' . app()->getLocale() . '.png') }}"
                        alt="" />
                </div>

                <!-- Refund Text in the Middle -->
                <div class="refund-text" style="text-align: center;">
                    <span>{{ strtoupper(__('admin::app.sales.invoices.invoice-pdf.invoice')) }}</span><br><br>
                    <span style="color: #000;font-size:18px">شركة دار الوفاء لتجارة الجملة والتجزئة</span>
                </div>

                <!-- Right Image -->
                <div class="image" style="margin-right: 20px;">
                    <img style="height: 140px;"
                        src="{{ asset('themes/admin/default/build/assets/daralwafaa_logo.png') }}" alt="" />
                </div>
            </div>

        </div>

        <div class="row" style="padding: 5px">
            <div class="col-12">
                <div class="col-6">
                    <div class="merchant-details">
                        <div class="row">
                            <span class="label">رقم المرتجع: </span>
                            <span class="value">#{{ $refund->increment_id ?? $refund->id }}</span>
                        </div>

                        <div class="row">
                            <span class="label">@lang('admin::app.sales.invoices.invoice-pdf.date'): </span>
                            <span class="value">{{ core()->formatDate($refund->created_at, 'd-m-Y') }}</span>
                        </div>

                        <div style="padding-top: 20px">
                            <span
                                class="merchant-details-title">{{ core()->getConfigData('sales.shipping.origin.store_name') ? core()->getConfigData('sales.shipping.origin.store_name') : '' }}</span>
                        </div>

                        <div>{{ core()->getConfigData('sales.shipping.origin.address1') ?? '' }}</div>

                        <div>
                            <span>{{ core()->getConfigData('sales.shipping.origin.zipcode') ?? '' }}</span>
                            <span>{{ core()->getConfigData('sales.shipping.origin.city') ?? '' }}</span>
                        </div>

                        <div>{{ core()->getConfigData('sales.shipping.origin.state') ?? '' }}</div>

                        <div>{{ core()->getConfigData('sales.shipping.origin.country') ?? '' }}</div>
                    </div>
                    <div class="merchant-details">
                        @if (core()->getConfigData('sales.shipping.origin.contact'))
                            <div><span class="merchant-details-title">@lang('admin::app.sales.invoices.invoice-pdf.contact-number'): </span>
                                {{ core()->getConfigData('sales.shipping.origin.contact') }}</div>
                        @endif

                        @if (core()->getConfigData('sales.shipping.origin.vat_number'))
                            <div><span class="merchant-details-title">@lang('admin::app.sales.invoices.invoice-pdf.vat-number'): </span>
                                {{ core()->getConfigData('sales.shipping.origin.vat_number') }}</div>
                        @endif
                    </div>
                </div>

                <div class="col-6" style="padding-left: 80px">
                    <div class="row">
                        <span class="label">@lang('admin::app.sales.invoices.invoice-pdf.order-id'): </span>
                        <span class="value">#{{ $refund->order->increment_id }}</span>
                    </div>

                    <div class="row">
                        <span class="label">@lang('admin::app.sales.invoices.invoice-pdf.order-date'): </span>
                        <span class="value">{{ core()->formatDate($refund->order->created_at, 'd-m-Y') }}</span>
                    </div>

                    @if (core()->getConfigData('sales.shipping.origin.bank_details'))
                        <div class="row" style="padding-top: 20px">
                            <span class="merchant-details-title">
                                @lang('admin::app.sales.invoices.invoice-pdf.bank-details'):
                            </span>
                            <div>{{ core()->getConfigData('sales.shipping.origin.bank_details') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="refund-summary">
            <!-- Billing & Shipping Address Details -->
            <div class="table address">
                <table>
                    <thead>
                        <tr>
                            <th class="table-header align-left" style="width: 50%;">
                                {{ ucwords(trans('admin::app.sales.invoices.invoice-pdf.bill-to')) }}
                            </th>

                            @if ($refund->order->shipping_address)
                                <th class="table-header align-left">
                                    {{ ucwords(trans('admin::app.sales.invoices.invoice-pdf.ship-to')) }}
                                </th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            @foreach (['billing_address', 'shipping_address'] as $addressType)
                                @if ($refund->order->$addressType)
                                    <td>
                                        <p>{{ $refund->order->$addressType->company_name ?? '' }}</p>

                                        <p>{{ $refund->order->$addressType->name }}</p>

                                        <p>
                                            @if ($refund->order->$addressType->country == 'KW')
                                                <?php $address1 = explode(PHP_EOL, $refund->order->$addressType->address1); ?>
                                                @if (isset($address1[0]))
                                                    {{ trans('shop::app.checkout.onepage.addresses.billing.block-address') }}
                                                    :
                                                    {{ $address1[0] ?? null }}<br>
                                                @endif
                                                @if (isset($address1[1]))
                                                    {{ trans('shop::app.checkout.onepage.addresses.billing.street-address') }}
                                                    :
                                                    {{ $address1[1] ?? null }}<br>
                                                @endif
                                                @if (isset($address1[3]))
                                                    {{ trans('shop::app.checkout.onepage.addresses.billing.house-address') }}
                                                    :
                                                    {{ (isset($address1[2]) ? 'House No:' . $address1[2] : '') . (isset($address1[3]) ? ' / Floor: ' . $address1[3] : '') . (isset($address1[4]) ? ' / Flat: ' . $address1[4] : '') }}<br>
                                                @endif
                                                @if (isset($address1[5]) && $address1[5] != null)
                                                    {{ trans('shop::app.checkout.onepage.addresses.billing.avenue-address') }}
                                                    :
                                                    {{ $address1[5] ?? null }}<br>
                                                @endif
                                            @else
                                                {{ $refund->order->$addressType->address1 }}<br>
                                            @endif
                                        </p>

                                        @if (isset($refund->order->$addressType->note) && $refund->order->$addressType->note != null)
                                            <p>
                                                {{ trans('shop::app.checkout.onepage.addresses.billing.note-address') }}
                                                :
                                                {{ $refund->order->$addressType->note }}<br>
                                            </p>
                                        @endif

                                        <p>{{ $refund->order->$addressType->postcode . ' ' . $refund->order->$addressType->city }}
                                        </p>

                                        <p>{{ $refund->order->$addressType->state }}</p>

                                        <p>{{ core()->country_name($refund->order->$addressType->country) }}</p>

                                        @lang('admin::app.sales.invoices.invoice-pdf.contact') : {{ $refund->order->$addressType->phone }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Payment & Shipping Methods -->
            <div class="table payment-shipment">
                <table>
                    <thead>
                        <tr>
                            <th class="table-header align-left" style="width: 50%;">@lang('admin::app.sales.invoices.invoice-pdf.payment-method')</th>

                            @if ($refund->order->shipping_address)
                                <th class="table-header align-left">@lang('admin::app.sales.invoices.invoice-pdf.shipping-method')</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>
                                {{ core()->getConfigData('sales.payment_methods.' . $refund->order->payment->method . '.title') }}

                                @php $additionalDetails = \Webkul\Payment\Payment::getAdditionalDetails($refund->order->payment->method); @endphp

                                @if (!empty($additionalDetails))
                                    <div>
                                        <label class="label">{{ $additionalDetails['title'] }}:</label>
                                        <p class="value">{{ $additionalDetails['value'] }}</p>
                                    </div>
                                @endif
                            </td>

                            @if ($refund->order->shipping_address)
                                <td>
                                    {{ $refund->order->shipping_title }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table items">
                <table>
                    <thead>
                        <tr>
                            @if (isset($refund->items->first()->additional['extra_qty']))
                                @foreach (['sku', 'product-name', 'price', 'qty', 'extra-qty', 'subtotal', 'discount', 'grand-total'] as $item)
                                    <th class="text-center table-header">@lang('admin::app.sales.invoices.invoice-pdf.' . $item)</th>
                                @endforeach
                            @else
                                @foreach (['sku', 'product-name', 'price', 'qty', 'subtotal', 'discount', 'grand-total'] as $item)
                                    <th class="text-center table-header">@lang('admin::app.sales.invoices.invoice-pdf.' . $item)</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($refund->items as $item)
                            <tr>
                                <td class="text-center">{{ $item->sku }}</td>

                                <td class="text-center">
                                    {{ $item->name }}

                                    @if (isset($item->additional['attributes']))
                                        <div class="item-options">

                                            @foreach ($item->additional['attributes'] as $attribute)
                                                <b>{{ $attribute['attribute_name'] }} :
                                                </b>{{ $attribute['option_label'] }}</br>
                                            @endforeach

                                        </div>
                                    @endif
                                </td>

                                <td class="text-center">{!! core()->formatBasePrice($item->base_price, true) !!}</td>

                                <td class="text-center">{{ $item->qty }}</td>
                                @if (isset($refund->items->first()->additional['extra_qty']))
                                    <td class="text-center">{{ $item->additional['extra_qty'] ?? 0 }}</td>
                                @endif
                                <td class="text-center">{!! core()->formatBasePrice($item->base_total, true) !!}</td>

                                <td class="text-center">{!! core()->formatBasePrice($item->discount_amount, true) !!}</td>

                                <td class="text-center">{!! core()->formatBasePrice($item->base_total + $item->base_tax_amount - $item->discount_amount, true) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Sale Summary -->
            <table class="sale-summary">
                <tr>
                    <td>@lang('admin::app.sales.invoices.invoice-pdf.subtotal')</td>
                    <td>-</td>
                    <td>{!! core()->formatBasePrice($refund->base_sub_total, true) !!}</td>
                </tr>

                <tr>
                    <td>@lang('admin::app.sales.invoices.invoice-pdf.shipping-handling')</td>
                    <td>-</td>
                    <td>{!! core()->formatBasePrice($refund->base_shipping_amount, true) !!}</td>
                </tr>

                <tr>
                    <td>@lang('admin::app.sales.invoices.invoice-pdf.tax')</td>
                    <td>-</td>
                    <td>{!! core()->formatBasePrice($refund->base_tax_amount, true) !!}</td>
                </tr>

                <tr>
                    <td>@lang('admin::app.sales.invoices.invoice-pdf.discount')</td>
                    <td>-</td>
                    <td>{!! core()->formatBasePrice($refund->base_discount_amount, true) !!}</td>
                </tr>

                <tr>
                    <td colspan="3">
                        <hr>
                    </td>
                </tr>

                <tr>
                    <td>@lang('admin::app.sales.invoices.invoice-pdf.grand-total')</td>
                    <td>-</td>
                    <td>{!! core()->formatBasePrice($refund->base_grand_total, true) !!}</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
