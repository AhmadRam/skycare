<?php

namespace Webkul\Admin\Http\Controllers\Reporting;

use Illuminate\Support\Facades\DB;
use Webkul\Admin\DataGrids\Sales\BrandProductSalesDataGrid;
use Webkul\Admin\DataGrids\Sales\BrandsSalesDataGrid;
use Webkul\Admin\DataGrids\Sales\CouponCodesDataGrid;
use Webkul\Admin\DataGrids\Sales\CustomersSalesDataGrid;
use Webkul\Admin\DataGrids\Sales\OrderItemSalesDataGrid;
use Webkul\Admin\DataGrids\Sales\ProductSalesDataGrid;

class SaleController extends Controller
{
    /**
     * Request param functions.
     *
     * @var array
     */
    protected $typeFunctions = [
        'total-sales'         => 'getTotalSalesStats',
        'average-sales'       => 'getAverageSalesStats',
        'total-orders'        => 'getTotalOrdersStats',
        'purchase-funnel'     => 'getPurchaseFunnelStats',
        'abandoned-carts'     => 'getAbandonedCartsStats',
        'refunds'             => 'getRefundsStats',
        'tax-collected'       => 'getTaxCollectedStats',
        'shipping-collected'  => 'getShippingCollectedStats',
        'top-payment-methods' => 'getTopPaymentMethods',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin::reporting.sales.index')->with([
            'startDate' => $this->reportingHelper->getStartDate(),
            'endDate'   => $this->reportingHelper->getEndDate(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function couponCodes()
    {
        if (request()->ajax()) {
            return app(CouponCodesDataGrid::class)->toJson();
        }

        return view('admin::reporting.sales.coupon-codes');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function couponCodeDetails($id)
    {
        // Get the cart rule coupon
        $cartRule = app('Webkul\CartRule\Repositories\CartRuleCouponRepository')
            ->where('cart_rule_id', $id)
            ->first();

        // Get order IDs that used this coupon
        $orderIds = DB::table('orders')
            ->where(function ($query) use ($id) {
                $query->where('applied_cart_rule_ids', 'like', "%,{$id},%")
                    ->orWhere('applied_cart_rule_ids', 'like', "{$id},%")
                    ->orWhere('applied_cart_rule_ids', 'like', "%,{$id}")
                    ->orWhere('applied_cart_rule_ids', $id);
            })
            ->whereNotIn('status', ['no_status', 'canceled', 'closed', 'fraud'])
            ->pluck('id');

        // Then get the product breakdown with proportional discount allocation
        $orderItems = DB::table('order_items')
            ->select(
                'product_attribute_values.integer_value as attribute_value_id',
                'attribute_options.admin_name as attribute_value_label',
                DB::raw('SUM(order_items.qty_ordered) as total_quantity'),
                DB::raw('SUM(
                    (order_items.base_price * order_items.qty_ordered) /
                    NULLIF((
                        SELECT SUM(oi.base_price * oi.qty_ordered)
                        FROM order_items oi
                        WHERE oi.order_id = orders.id
                    ), 0) * orders.base_discount_amount
                ) as base_total')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_flat', function ($join) {
                $join->on('order_items.product_id', '=', 'product_flat.product_id')
                    ->where('product_flat.locale', 'en');
            })
            ->join('product_attribute_values', function ($join) {
                $join->on('product_flat.product_id', '=', 'product_attribute_values.product_id')
                    ->where('product_attribute_values.attribute_id', 25);
            })
            ->join('attribute_options', 'product_attribute_values.integer_value', '=', 'attribute_options.id')
            ->where('orders.base_discount_amount', '>', 0)
            ->whereIn('orders.id', $orderIds)
            ->groupBy('product_attribute_values.integer_value', 'attribute_options.admin_name')
            ->get();

        return view('admin::reporting.sales.coupon-codes-details', [
            'orderItems' => $orderItems,
            'cart_rulte' => $cartRule
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function view()
    {
        return view('admin::reporting.view')->with([
            'entity'    => 'sales',
            'startDate' => $this->reportingHelper->getStartDate(),
            'endDate'   => $this->reportingHelper->getEndDate(),
        ]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function productSales()
    {
        if (request()->ajax()) {
            return app(ProductSalesDataGrid::class)->toJson();
        }

        return view('admin::reporting.sales.product-sales');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function brandsSales()
    {
        if (request()->ajax()) {
            return app(BrandsSalesDataGrid::class)->toJson();
        }

        return view('admin::reporting.sales.brands-sales');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function brandsSalesDetails($id, $customer_group_id)
    {
        if (request()->ajax()) {
            request()->merge(['brand_id' => $id, 'customer_group_id' => $customer_group_id]);
            return app(BrandProductSalesDataGrid::class)->toJson();
        }

        return view('admin::reporting.sales.brands-sales-details', compact('id', 'customer_group_id'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function orderItemSales()
    {
        if (request()->ajax()) {
            return app(OrderItemSalesDataGrid::class)->toJson();
        }

        return view('admin::reporting.sales.order-items-sales');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function customersSales()
    {
        if (request()->ajax()) {
            return app(CustomersSalesDataGrid::class)->toJson();
        }

        return view('admin::reporting.sales.customers-sales');
    }
}
