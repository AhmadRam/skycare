<?php

return [
    'flatrate' => [
        'code'         => 'flatrate',
        'title'        => 'Flat Rate',
        'description'  => 'Flat Rate Shipping',
        'active'       => true,
        'default_rate' => '10',
        'type'         => 'per_unit',
        'class'        => 'Webkul\Shipping\Carriers\FlatRate',
    ],

    'free' => [
        'code'         => 'free',
        'title'        => 'Free Shipping',
        'description'  => 'Free Shipping',
        'active'       => true,
        'default_rate' => '0',
        'class'        => 'Webkul\Shipping\Carriers\Free',
    ],

    'internal' => [
        'code'         => 'internal',
        'title'        => 'Internal Shipping',
        'description'  => 'Internal Shipping',
        'active'       => true,
        'default_rate' => '1',
        'class'        => 'Webkul\Shipping\Carriers\Internal',
    ],

    'custom' => [
        'code'         => 'custom',
        'title'        => 'custom Shipping',
        'description'  => 'custom Shipping',
        'active'       => true,
        'default_rate' => '1',
        'class'        => 'Webkul\Shipping\Carriers\Custom',
    ],
];
