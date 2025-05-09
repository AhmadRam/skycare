<?php

namespace Webkul\Admin\Helpers\Reporting;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\RefundRepository;

class Sale extends AbstractReporting
{
    /**
     * Create a helper instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected OrderItemRepository $orderItemRepository,
        protected InvoiceRepository $invoiceRepository,
        protected RefundRepository $refundRepository
    ) {
        parent::__construct();
    }

    /**
     * Retrieves total orders and their progress.
     *
     * @return array
     */
    public function getTotalOrdersProgress($condition = ['!=', 3])
    {
        return [
            'previous' => $previous = $this->getTotalOrders($this->lastStartDate, $this->lastEndDate, $condition),
            'current'  => $current = $this->getTotalOrders($this->startDate, $this->endDate, $condition),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Returns previous orders over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getPreviousTotalOrdersOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getTotalOrdersOverTime($this->lastStartDate, $this->lastEndDate, $period, $includeEmpty);
    }

    /**
     * Returns current orders over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getCurrentTotalOrdersOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getTotalOrdersOverTime($this->startDate, $this->endDate, $period, $includeEmpty);
    }

    /**
     * Retrieves total orders
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getTotalOrders($startDate, $endDate, $group_condition = ['!=', 3]): int
    {
        return $this->orderRepository
            ->resetModel()
            ->leftjoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.customer_group_id')
            ->where(function ($query) use ($group_condition) {
                $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1]);
                if ($group_condition[0] == "!=") {
                    $query->orWhereNull('orders.customer_id');
                }
            })->where('orders.status', '!=', 'no_status')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Returns orders over time
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getTotalOrdersOverTime($startDate, $endDate, $period, $includeEmpty): array
    {
        return $this->getOverTimeStats(
            $startDate,
            $endDate,
            'COUNT(*)',
            $period
        );
    }

    /**
     * Retrieves today orders and their progress.
     */
    public function getTodayOrdersProgress($condition = ['!=', 3]): array
    {
        return [
            'previous' => $previous = $this->getTotalOrders(now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), $condition),
            'current'  => $current = $this->getTotalOrders(now()->today(), now()->endOfDay(), $condition),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves orders
     *
     * @return object
     */
    public function getTodayOrders($condition = ['!=', 3])
    {
        return $this->orderRepository
            ->resetModel()
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.customer_group_id')
            ->where(function ($query) use ($condition) {
                $query->where('customers.customer_group_id', $condition[0], $condition[1]);
                if ($condition[0] == "!=") {
                    $query->orWhereNull('orders.customer_id');
                }
            })->where('orders.status', '!=', 'no_status')
            ->with(['addresses', 'payment', 'items'])
            ->whereBetween('orders.created_at', [now()->today(), now()->endOfDay()])
            ->get();
    }

    /**
     * Retrieves total sales and their progress.
     */
    public function getTotalSalesProgress($condition = ['!=', 3]): array
    {
        return [
            'previous'        => $previous = $this->getTotalSales($this->lastStartDate, $this->lastEndDate, $condition),
            'current'         => $current = $this->getTotalSales($this->startDate, $this->endDate, $condition),
            'formatted_total' => core()->formatBasePrice($current),
            'progress'        => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves total unpaid sales and their progress.
     */
    public function getTotalUnpaidSalesProgress($condition = ['!=', 3]): array
    {
        return [
            'previous'        => $previous = $this->getTotalUnpaidSales($this->lastStartDate, $this->lastEndDate, $condition),
            'current'         => $current = $this->getTotalUnpaidSales($this->startDate, $this->endDate, $condition),
            'formatted_total' => core()->formatBasePrice($current),
            'progress'        => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves total unpaid sales and their progress.
     */
    public function getTotalPaidSalesProgress($condition = ['!=', 3]): array
    {
        return [
            'previous'        => $previous = $this->getTotalPaidSales($this->lastStartDate, $this->lastEndDate, $condition),
            'current'         => $current = $this->getTotalPaidSales($this->startDate, $this->endDate, $condition),
            'formatted_total' => core()->formatBasePrice($current),
            'progress'        => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves total refunded sales and their progress.
     */
    public function getTotalRefundedSalesProgress($condition = ['!=', 3]): array
    {
        return [
            'previous'        => $previous = $this->getTotalRefundedSales($this->lastStartDate, $this->lastEndDate, $condition),
            'current'         => $current = $this->getTotalRefundedSales($this->startDate, $this->endDate, $condition),
            'formatted_total' => core()->formatBasePrice($current),
            'progress'        => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves sub total sales and their progress.
     */
    public function getSubTotalSalesProgress(): array
    {
        return [
            'previous'        => $previous = $this->getSubTotalSales($this->lastStartDate, $this->lastEndDate),
            'current'         => $current = $this->getSubTotalSales($this->startDate, $this->endDate),
            'formatted_total' => core()->formatBasePrice($current),
            'progress'        => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves today sales and their progress.
     */
    public function getTodaySalesProgress($condition = ['!=', 3]): array
    {
        return [
            'previous'        => $previous = $this->getTotalSales(now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), $condition),
            'current'         => $current = $this->getTotalSales(now()->today(), now()->endOfDay(), $condition),
            'formatted_total' => core()->formatBasePrice($current),
            'progress'        => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves total sales
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getTotalSales($startDate, $endDate, $group_condition = ['!=', 3]): float
    {
        return $this->orderRepository
            ->resetModel()
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.customer_group_id')
            ->where(function ($query) use ($group_condition) {
                $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1]);
                if ($group_condition[0] == "!=") {
                    $query->orWhereNull('orders.customer_id');
                }
            })
            ->where('orders.status', '!=', 'no_status')
            ->where('orders.status', '!=', 'closed')
            ->where('orders.status', '!=', 'canceled')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum(DB::raw('base_grand_total'));
    }

    /**
     * Retrieves total Unpaid sales
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getTotalUnpaidSales($startDate, $endDate, $group_condition = ['!=', 3]): float
    {
        return $this->orderRepository
            ->resetModel()
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.customer_group_id')
            ->where(function ($query) use ($group_condition) {
                $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1]);
                if ($group_condition[0] == "!=") {
                    $query->orWhereNull('orders.customer_id');
                }
            })->where('orders.status', 'pending')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum(DB::raw('base_grand_total'));
    }

    /**
     * Retrieves total Unpaid sales
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getTotalPaidSales($startDate, $endDate, $group_condition = ['!=', 3]): float
    {
        return $this->orderRepository
            ->resetModel()
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.customer_group_id')
            ->where(function ($query) use ($group_condition) {
                $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1]);
                if ($group_condition[0] == "!=") {
                    $query->orWhereNull('orders.customer_id');
                }
            })->where('orders.status', '!=', 'no_status')
            ->where('orders.status', '!=', 'closed')
            ->where('orders.status', '!=', 'canceled')
            ->where('orders.status', '!=', 'pending')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum(DB::raw('base_grand_total'));
    }

    /**
     * Retrieves total refunded sales
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getTotalRefundedSales($startDate, $endDate, $group_condition = ['!=', 3]): float
    {
        return $this->orderRepository
            ->resetModel()
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.customer_group_id')
            ->where(function ($query) use ($group_condition) {
                $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1]);
                if ($group_condition[0] == "!=") {
                    $query->orWhereNull('orders.customer_id');
                }
            })->where('orders.status', '!=', 'no_status')
            ->whereIn('orders.status', ['closed', 'canceled'])
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum(DB::raw('base_grand_total'));
    }

    /**
     * Retrieves sub total sales
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     */
    public function getSubTotalSales($startDate, $endDate): float
    {
        return $this->orderRepository
            ->resetModel()
            ->where('status', '!=', 'no_status')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('base_sub_total_invoiced - base_sub_total_refunded'));
    }

    /**
     * Returns previous sales over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getPreviousTotalSalesOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getTotalSalesOverTime($this->lastStartDate, $this->lastEndDate, $period, $includeEmpty);
    }

    /**
     * Returns current sales over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getCurrentTotalSalesOverTime($period = 'auto', $includeEmpty = true, $condition = ['!=', 3]): array
    {
        return $this->getTotalSalesOverTime($this->startDate, $this->endDate, $period, $includeEmpty, $condition);
    }

    /**
     * Returns sales over time
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getTotalSalesOverTime($startDate, $endDate, $period, $includeEmpty, $condition = ['!=', 3]): array
    {
        return $this->getOverTimeStats(
            $startDate,
            $endDate,
            // 'SUM(base_grand_total_invoiced - base_grand_total_refunded)',
            'SUM(base_grand_total)',
            $period,
            $condition
        );
    }

    /**
     * Retrieves average sales and their progress.
     */
    public function getAverageSalesProgress($condition = ['!=', 3]): array
    {
        return [
            'previous'        => $previous = $this->getAverageSales($this->lastStartDate, $this->lastEndDate, $condition),
            'current'         => $current = $this->getAverageSales($this->startDate, $this->endDate, $condition),
            'formatted_total' => core()->formatBasePrice($current),
            'progress'        => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves average sales
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    public function getAverageSales($startDate, $endDate, $group_condition = ['!=', 3]): ?float
    {
        return $this->orderRepository
            ->resetModel()
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.*', 'customers.customer_group_id')
            ->where(function ($query) use ($group_condition) {
                $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1]);
                if ($group_condition[0] == "!=") {
                    $query->orWhereNull('orders.customer_id');
                }
            })->where('orders.status', '!=', 'no_status')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->avg(DB::raw('base_grand_total_invoiced - base_grand_total_refunded'));
    }

    /**
     * Returns previous average sales over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getPreviousAverageSalesOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getAverageSalesOverTime($this->lastStartDate, $this->lastEndDate, $period, $includeEmpty);
    }

    /**
     * Returns current average sales over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getCurrentAverageSalesOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getAverageSalesOverTime($this->startDate, $this->endDate, $period, $includeEmpty);
    }

    /**
     * Returns average sales over time
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getAverageSalesOverTime($startDate, $endDate, $period, $includeEmpty): array
    {
        return $this->getOverTimeStats(
            $startDate,
            $endDate,
            'AVG(base_grand_total_invoiced - base_grand_total_refunded)',
            $period
        );
    }

    /**
     * Retrieves refunds and their progress.
     */
    public function getRefundsProgress(): array
    {
        return [
            'previous'        => $previous = $this->getRefunds($this->lastStartDate, $this->lastEndDate),
            'current'         => $current = $this->getRefunds($this->startDate, $this->endDate),
            'formatted_total' => core()->formatBasePrice($current),
            'progress'        => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves refunds
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    public function getRefunds($startDate, $endDate): float
    {
        return $this->orderRepository
            ->resetModel()
            ->where('status', '!=', 'no_status')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('base_grand_total_refunded'));
    }

    /**
     * Returns previous refunds over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getPreviousRefundsOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getRefundsOverTime($this->lastStartDate, $this->lastEndDate, $period, $includeEmpty);
    }

    /**
     * Returns current refunds over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getCurrentRefundsOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getRefundsOverTime($this->startDate, $this->endDate, $period, $includeEmpty);
    }

    /**
     * Returns refunds over time
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getRefundsOverTime($startDate, $endDate, $period, $includeEmpty): array
    {
        return $this->getOverTimeStats(
            $startDate,
            $endDate,
            'SUM(base_grand_total_refunded)',
            $period
        );
    }

    /**
     * Retrieves tax collected and their progress.
     */
    public function getTaxCollectedProgress(): array
    {
        return [
            'previous'        => $previous = $this->getTaxCollected($this->lastStartDate, $this->lastEndDate),
            'current'         => $current = $this->getTaxCollected($this->startDate, $this->endDate),
            'formatted_total' => core()->formatBasePrice($current),
            'progress'        => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves tax collected
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    public function getTaxCollected($startDate, $endDate): float
    {
        return $this->orderRepository
            ->resetModel()
            ->where('status', '!=', 'no_status')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('base_tax_amount_invoiced - base_tax_amount_refunded'));
    }

    /**
     * Returns previous tax collected over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getPreviousTaxCollectedOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getTaxCollectedOverTime($this->lastStartDate, $this->lastEndDate, $period, $includeEmpty);
    }

    /**
     * Returns current tax collected over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getCurrentTaxCollectedOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getTaxCollectedOverTime($this->startDate, $this->endDate, $period, $includeEmpty);
    }

    /**
     * Returns tax collected over time
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getTaxCollectedOverTime($startDate, $endDate, $period, $includeEmpty): array
    {
        return $this->getOverTimeStats(
            $startDate,
            $endDate,
            'SUM(base_tax_amount_invoiced - base_tax_amount_refunded)',
            $period
        );
    }

    /**
     * Returns top tax categories
     *
     * @param  int  $limit
     */
    public function getTopTaxCategories($limit = null): Collection
    {
        return $this->orderItemRepository
            ->resetModel()
            ->leftJoin('tax_categories', 'order_items.tax_category_id', '=', 'tax_categories.id')
            ->select('tax_categories.id as tax_category_id', 'tax_categories.name')
            ->addSelect(DB::raw('SUM(base_tax_amount_invoiced - base_tax_amount_refunded) as total'))
            ->whereBetween('order_items.created_at', [$this->startDate, $this->endDate])
            ->whereNotNull('tax_category_id')
            ->groupBy('tax_category_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * Retrieves shipping collected and their progress.
     */
    public function getShippingCollectedProgress(): array
    {
        return [
            'previous'        => $previous = $this->getShippingCollected($this->lastStartDate, $this->lastEndDate),
            'current'         => $current = $this->getShippingCollected($this->startDate, $this->endDate),
            'formatted_total' => core()->formatBasePrice($current),
            'progress'        => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Retrieves shipping collected
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    public function getShippingCollected($startDate, $endDate): float
    {
        return $this->orderRepository
            ->resetModel()
            ->where('status', '!=', 'no_status')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('base_shipping_invoiced - base_shipping_refunded'));
    }

    /**
     * Returns previous shipping collected over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getPreviousShippingCollectedOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getShippingCollectedOverTime($this->lastStartDate, $this->lastEndDate, $period, $includeEmpty);
    }

    /**
     * Returns current shipping collected over time
     *
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getCurrentShippingCollectedOverTime($period = 'auto', $includeEmpty = true): array
    {
        return $this->getShippingCollectedOverTime($this->startDate, $this->endDate, $period, $includeEmpty);
    }

    /**
     * Returns shipping collected over time
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $period
     * @param  bool  $includeEmpty
     */
    public function getShippingCollectedOverTime($startDate, $endDate, $period, $includeEmpty): array
    {
        return $this->getOverTimeStats(
            $startDate,
            $endDate,
            'SUM(base_shipping_invoiced - base_shipping_refunded)',
            $period
        );
    }

    /**
     * Returns top shipping methods
     *
     * @param  int  $limit
     */
    public function getTopShippingMethods($limit = null): Collection
    {
        return $this->orderRepository
            ->resetModel()
            ->where('status', '!=', 'no_status')
            ->select('shipping_title as title')
            ->addSelect(DB::raw('SUM(base_shipping_invoiced - base_shipping_refunded) as total'))
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotNull('shipping_method')
            ->groupBy('shipping_method')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * Returns top payment methods
     *
     * @param  int  $limit
     */
    public function getTopPaymentMethods($limit = null): Collection
    {
        return $this->orderRepository
            ->resetModel()
            ->where('status', '!=', 'no_status')
            ->leftJoin('order_payment', 'orders.id', '=', 'order_payment.order_id')
            ->select('method', 'method_title as title')
            ->addSelect(DB::raw('COUNT(*) as total'))
            ->addSelect(DB::raw('SUM(base_grand_total) as base_total'))
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->groupBy('method')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    /**
     * Gets the total amount of pending invoices.
     */
    public function getTotalPendingInvoicesAmount(): float
    {
        return $this->invoiceRepository->getTotalPendingInvoicesAmount();
    }

    /**
     * Retrieves total unique cart users
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    public function getTotalUniqueOrdersUsers($startDate, $endDate): int
    {
        return $this->orderRepository
            ->resetModel()
            ->where('status', '!=', 'no_status')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('CONCAT(customer_email, "-", customer_id)'))
            ->get()
            ->count();
    }

    /**
     * Returns over time stats.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $valueColumn
     * @param  string  $period
     */
    public function getOverTimeStats($startDate, $endDate, $valueColumn, $period = 'auto', $group_condition = ['!=', 3]): array
    {
        $config = $this->getTimeInterval($startDate, $endDate, $period, 'orders.');

        $groupColumn = $config['group_column'];

        $results = $this->orderRepository
            ->resetModel()
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->where(function ($query) use ($group_condition) {
                $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1]);
                if ($group_condition[0] == "!=") {
                    $query->orWhereNull('orders.customer_id');
                }
            })
            ->select(
                DB::raw("$groupColumn AS date"),
                DB::raw("$valueColumn AS total"),
                DB::raw('COUNT(*) AS count'),
                // DB::raw("AVG(total/count) AS avg"),
            )
            ->where('orders.status', '!=', 'no_status')
            ->where('orders.status', '!=', 'closed')
            ->where('orders.status', '!=', 'canceled')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->get();

        // $orderSummary = $this->orderRepository
        //     ->select(
        //         DB::raw("$groupColumn AS date"),
        //         DB::raw("$valueColumn AS total"),
        //         DB::raw('COUNT(*) AS count'),
        //         'customer_id'
        //     )
        //     ->where('status', '!=', 'no_status')
        //     ->whereBetween('created_at', [$startDate, $endDate])
        //     ->groupBy('date', 'customer_id');


        // $results = $this->orderRepository
        //     ->resetModel()
        //     ->join('customers', 'orders.customer_id', '=', 'customers.id')
        //     ->leftJoinSub($orderSummary, 'order_summary', function ($join) {
        //         $join->on('customers.id', '=', 'order_summary.customer_id');
        //     })
        //     ->where(function ($query) use ($group_condition) {
        //         $query->where('customers.customer_group_id', $group_condition[0], $group_condition[1]);
        //         if ($group_condition[0] == "!=") {
        //             $query->orWhereNull('orders.customer_id');
        //         }
        //     })->select(
        //         'order_summary.date',
        //         'order_summary.total',
        //         'order_summary.count'
        //     )->get();


        foreach ($config['intervals'] as $interval) {
            $total = $results->where('date', $interval['filter'])->first();
            $stats[] = [
                'label' => $interval['start'],
                // 'avg' => ($total?->total ?? 1) / ($total?->count ?? 1),
                'total' => $total?->total ?? 0,
                'count' => $total?->count ?? 0,
            ];
        }

        return $stats ?? [];
    }
}
