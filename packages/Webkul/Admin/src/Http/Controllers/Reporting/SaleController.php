<?php

namespace Webkul\Admin\Http\Controllers\Reporting;

use Illuminate\Support\Facades\DB;
use Webkul\Admin\DataGrids\Sales\BrandProductSalesDataGrid;
use Webkul\Admin\DataGrids\Sales\BrandsSalesDataGrid;
use Webkul\Admin\DataGrids\Sales\CouponCodesDataGrid;
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
        $cart_rulte = app('Webkul\CartRule\Repositories\CartRuleCouponRepository')->where('cart_rule_id', $id)->first();

        $orders = app('Webkul\Sales\Repositories\OrderRepository')->where(function ($query) use ($id) {
            foreach (explode(',', $id) as $ruleId) {
                $query->orWhere('applied_cart_rule_ids', 'like', '%' . $ruleId . '%');
            }
        })->pluck('id')->toArray();

        $attributeId = 25;

        $orderItems = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_flat', 'order_items.product_id', '=', 'product_flat.product_id')
            ->join('product_attribute_values', 'product_flat.product_id', '=', 'product_attribute_values.product_id')
            ->join('attribute_options', 'product_attribute_values.integer_value', '=', 'attribute_options.id')

            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->join('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
            ->where(function ($query) {
                $query->where('customers.customer_group_id', '!=', 3)->orWhereNull('orders.customer_id');
            })

            ->where('product_attribute_values.attribute_id', $attributeId)
            ->where('order_items.discount_amount', '!=', 0)
            ->whereNotIn('orders.status', ['no_status', 'canceled', 'closed', 'fraud'])
            ->where('product_flat.locale', 'en')
            ->whereIn('orders.id', $orders)
            ->groupBy('product_attribute_values.integer_value', 'attribute_options.admin_name')
            ->select(
                'product_attribute_values.integer_value as attribute_value_id',
                'attribute_options.admin_name as attribute_value_label',
                DB::raw('SUM(order_items.base_total) as base_total'),
                DB::raw('SUM(order_items.qty_ordered) as total_quantity')
            )
            ->get();

        return view('admin::reporting.sales.coupon-codes-details', compact('orderItems', 'cart_rulte'));
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
}
