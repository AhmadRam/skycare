<?php
return [
    'myfatoorah' => [
        'code'             => 'myfatoorah',
        'title'            => 'Myfatoorah',
        'description'      => 'Myfatoorah',
        'class'            => 'Webkul\Myfatoorah\Payment\Standard',
        'sandbox'          => false,
        'active'           => true,
        'api_test_key'     => 'rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL',
        'api_key'          => 'DJzkZSXzYv6k_uRRU-pwCnNyvhUJwTerXnFau4QgJANFqAO2-9rb4B0-KCxXOjKq7j5uVONkWSEhPi4mxY0fAvhLIMd32LX3ZZ573sHTAdV5L4bbVOpHWoC4Czvbg5AxhmxBDIfcfMn8BM4L-7WxZzl_dLjhw7Kqd5pPKT480qkEkduDK0ICcc3D4oyxmdxX0CTv9MtktzLQg2bIVqZOU_cYqHLSUjrmCtbH6FYRvYkQWVdqdp2Gy2v_4WUG4IJGN19mfaTe963Gm10D-mCypuP6bBqlZQ-mEMmsC7JXMHlBSm-CblnW2s7h6HltxtDAAnvB-wRr3s21umkH6h-Tqsa-PscSydICRYUzvxK9jyxnZPrnHAiGxdCzwcxyDcMASMoHW2wuF-WEShLK0qRrpqIfQnAootj6mGtmfFvzGLznC6tcTXuYawvWwI8R3qQK2O8D5_TVKMrlcJD3WXgkkYzhFhv01te-6LQpfXcdyOR5MdAR4TkoR6CgNz8nDbZKb24zcGqvTc6uqOZJY3Ca8yPbRMWMkzrUHFKH8tB0q-82sZ7KJMeQKT_4v5epj_BkHHSyrlcWIstxoFozXB2OHbdCNzbnetpax_Tu2EiEdnlJxQaIUbYUm69FvBzxLo-VDAO539Tup290Xcwy26Yb-XeimbmWbwqPyDbAcbgAf271TkMGMFfYzjsXjOvUuLJJpZn7hC7Dbg-fQsGzPVzCpqY-BSGHo83gdPXNWnorKP871hL3',
        'sort'             => 5,
    ],

    'visa/master' => [
        'code'             => 'visa/master',
        'title'            => 'Visa/Master',
        'description'      => 'Visa/Master',
        'class'            => 'Webkul\Myfatoorah\Payment\StandardVisaMaster',
        'sandbox'          => false,
        'active'           => true,
        'sort'             => 2,
    ],

    'applepay' => [
        'code'             => 'applepay',
        'title'            => 'Apple Pay',
        'description'      => 'Apple Pay',
        'class'            => 'Webkul\Myfatoorah\Payment\StandardApplePay',
        'sandbox'          => false,
        'active'           => true,
        'sort'             => 3,
    ],

    'googlepay' => [
        'code'             => 'googlepay',
        'title'            => 'Google Pay',
        'description'      => 'Google Pay',
        'class'            => 'Webkul\Myfatoorah\Payment\StandardGooglePay',
        'sandbox'          => false,
        'active'           => true,
        'sort'             => 4,
    ],
];
