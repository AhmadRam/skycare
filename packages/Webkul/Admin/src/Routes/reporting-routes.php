<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Reporting\CustomerController;
use Webkul\Admin\Http\Controllers\Reporting\ProductController;
use Webkul\Admin\Http\Controllers\Reporting\SaleController;

/**
 * Reporting routes.
 */
Route::group(['middleware' => ['admin'], 'prefix' => config('app.admin_url')], function () {
    Route::prefix('reporting')->group(function () {
        /**
         * Customer routes.
         */
        Route::controller(CustomerController::class)->prefix('customers')->group(function () {
            Route::get('', 'index')->name('admin.reporting.customers.index');

            Route::get('stats', 'stats')->name('admin.reporting.customers.stats');

            Route::get('export', 'export')->name('admin.reporting.customers.export');

            Route::get('view', 'view')->name('admin.reporting.customers.view');

            Route::get('view/stats', 'viewStats')->name('admin.reporting.customers.view.stats');
        });

        /**
         * Product routes.
         */
        Route::controller(ProductController::class)->prefix('products')->group(function () {
            Route::get('', 'index')->name('admin.reporting.products.index');

            Route::get('stats', 'stats')->name('admin.reporting.products.stats');

            Route::get('export', 'export')->name('admin.reporting.products.export');

            Route::get('view', 'view')->name('admin.reporting.products.view');

            Route::get('view/stats', 'viewStats')->name('admin.reporting.products.view.stats');

            Route::get('quantities', 'productQuantities')->name('admin.reporting.products.view.product-quantities');
        });

        /**
         * Sale routes.
         */
        Route::controller(SaleController::class)->prefix('sales')->group(function () {
            Route::get('', 'index')->name('admin.reporting.sales.index');

            Route::get('stats', 'stats')->name('admin.reporting.sales.stats');

            Route::get('export', 'export')->name('admin.reporting.sales.export');

            Route::get('view', 'view')->name('admin.reporting.sales.view');

            Route::get('view/stats', 'viewStats')->name('admin.reporting.sales.view.stats');

            Route::get('coupon-codes', 'couponCodes')->name('admin.reporting.coupon_codes_report.index');

            Route::get('coupon-codes/{id}', 'couponCodeDetails')->name('admin.reporting.coupon_codes_report.view');

            Route::get('brands-sales', 'brandsSales')->name('admin.reporting.brands_sales_report.index');

            Route::get('order-items-sales', 'orderItemSales')->name('admin.reporting.order_items_sales_report.index');

            Route::get('brands-sales-details/{id}/{customer_group_id}', 'brandsSalesDetails')->name('admin.reporting.brands_sales_report.view');

            Route::get('product-sales', 'productSales')->name('admin.reporting.product_sales_report.index');

            Route::get('customers-sales', 'customersSales')->name('admin.reporting.customers_sales.index');
        });
    });
});
