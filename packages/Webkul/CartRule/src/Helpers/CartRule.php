<?php

namespace Webkul\CartRule\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;
use Webkul\CartRule\Repositories\CartRuleCouponUsageRepository;
use Webkul\CartRule\Repositories\CartRuleCustomerRepository;
use Webkul\CartRule\Repositories\CartRuleRepository;
use Webkul\CatalogRule\Repositories\CatalogRuleRepository;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Rule\Helpers\Validator;

class CartRule
{
    /**
     * @var \Webkul\Checkout\Contracts\Cart
     */
    protected $cart = null;

    /**
     * @var array
     */
    protected $itemTotals = [];

    /**
     * @var array
     */
    protected $cartRules = null;

    /**
     * Create a new helper instance.
     *
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CartRepository $cartRepository,
        protected CartRuleRepository $cartRuleRepository,
        protected CartRuleCouponRepository $cartRuleCouponRepository,
        protected CartRuleCustomerRepository $cartRuleCustomerRepository,
        protected CartRuleCouponUsageRepository $cartRuleCouponUsageRepository,
        protected CatalogRuleRepository $catalogRuleRepository,
        protected Validator $validator
    ) {}

    /**
     * Collect discount on cart
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return void
     */
    public function collect($cart)
    {
        $this->cart = $cart;

        /**
         * If cart rules are not available then don't process further.
         */
        if (
            !$this->haveCartRules()
            && !(float) $cart->base_discount_amount
        ) {
            return;
        }

        $appliedCartRuleIds = [];

        $this->calculateCartItemTotals();

        $catalog_rule_skus = $this->getCatalogRuleSkus();
        foreach ($cart->items as $item) {
            $itemCartRuleIds = $this->process($item, $catalog_rule_skus);

            $appliedCartRuleIds = array_merge($appliedCartRuleIds, $itemCartRuleIds);

            if (
                $item->children()->count()
                && $item->getTypeInstance()->isChildrenCalculated()
            ) {
                $this->divideDiscount($item);
            }
        }

        $this->cart = $this->cartRepository->update([
            'applied_cart_rule_ids' => implode(',', array_unique($appliedCartRuleIds, SORT_REGULAR)),
        ], $this->cart->id);

        $this->processShippingDiscount();

        $this->processFreeShippingDiscount();

        if (!$this->checkCouponCode()) {
            cart()->removeCouponCode();
        }
    }

    /**
     * Returns cart rules
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCartRules()
    {
        if ($this->cartRules) {
            return $this->cartRules;
        }

        $this->cartRules = $this->getCartRuleQuery()
            ->with([
                'cart_rule_customer_groups',
                'cart_rule_channels',
                'cart_rule_coupon',
            ])
            ->get();

        return $this->cartRules;
    }

    /**
     * Check if cart rule can be applied
     *
     * @param  \Webkul\CartRule\Contracts\CartRule  $rule
     */
    public function canProcessRule($rule): bool
    {
        if ($rule->coupon_type) {
            if (!strlen($this->cart->coupon_code)) {
                return false;
            }

            /** @var \Webkul\CartRule\Models\CartRule $rule */
            // Laravel relation is used instead of repository for performance
            // reasons (cart_rule_coupon-relation is pre-loaded by self::getCartRuleQuery())
            $coupon = $rule->cart_rule_coupon()->where('code', $this->cart->coupon_code)->first();

            if (
                $coupon
                && $coupon->code === $this->cart->coupon_code
            ) {
                if (
                    $coupon->usage_limit
                    && $coupon->times_used >= $coupon->usage_limit
                ) {
                    return false;
                }

                if (
                    $this->cart->customer_id
                    && $coupon->usage_per_customer
                ) {
                    $couponUsage = $this->cartRuleCouponUsageRepository->findOneWhere([
                        'cart_rule_coupon_id' => $coupon->id,
                        'customer_id'         => $this->cart->customer_id,
                    ]);

                    if (
                        $couponUsage
                        && $couponUsage->times_used >= $coupon->usage_per_customer
                    ) {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }

        if ($rule->usage_per_customer) {
            $ruleCustomer = $this->cartRuleCustomerRepository->findOneWhere([
                'cart_rule_id' => $rule->id,
                'customer_id'  => $this->cart->customer_id,
            ]);

            if (
                $ruleCustomer
                && $ruleCustomer->times_used >= $rule->usage_per_customer
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cart item discount calculation process
     */
    public function process(CartItem $item, $catalog_rule_skus = []): array
    {
        $item->discount_percent = 0;
        $item->discount_amount = 0;
        $item->base_discount_amount = 0;

        $appliedRuleIds = [];

        foreach ($rules = $this->getCartRules() as $rule) {

            if (in_array($item->sku, $catalog_rule_skus)) {
                continue;
            }
            if ($rule->discount_amount == 0) {
                if (isset($item->additional['bundle_options'])) {
                    $items_count = sizeOf($item->additional['bundle_options'][1]);
                    // dd($items_count,$item->additional['bundle_options'][1]);
                    if ($items_count < 2) {
                        continue;
                    }
                } else {
                    $cart = Cart::getCart();
                    $skus = [];
                    foreach ($rule->conditions as $condition) {
                        if (isset($condition['value'])) {
                            $skus[] = $condition['value'];
                        }
                    }
                    $items_count = $cart->items->whereIn('sku', $skus)->sum('quantity');
                    if ($items_count < 2) {
                        continue;
                    }
                }
            }

            if (!$this->canProcessRule($rule)) {
                continue;
            }

            if (!$this->validator->validate($rule, $item)) {
                continue;
            }

            if ($rule->coupon_code) {
                $item->coupon_code = $rule->coupon_code;
            }

            $quantity = $rule->discount_quantity ? min($item->quantity, $rule->discount_quantity) : $item->quantity;

            $discountAmount = $baseDiscountAmount = 0;

            switch ($rule->action_type) {
                case 'by_percent':
                    $rulePercent = min(100, $rule->discount_amount);

                    if (isset($items_count)) {
                        $cond_rules = json_decode($rule->description, true);
                        if ($cond_rules != null) {
                            foreach ($cond_rules as $key => $value) {
                                if ($items_count == $key) {
                                    $rulePercent = min(100, $value);
                                    break;
                                }
                                if ($items_count > $key) {
                                    $rulePercent = min(100, $value);
                                }
                            }
                        }

                        // if ($items_count == 2) {
                        //     $rulePercent = min(100, 15);
                        // } else if ($items_count == 3) {
                        //     $rulePercent = min(100, 20);
                        // } else if ($items_count >= 4) {
                        //     $rulePercent = min(100, 30);
                        // }
                    }

                    $discountAmount = ($quantity * $item->price + $item->tax_amount - $item->discount_amount) * ($rulePercent / 100);

                    $baseDiscountAmount = ($quantity * $item->base_price + $item->base_tax_amount - $item->base_discount_amount) * ($rulePercent / 100);

                    if (
                        !$rule->discount_quantity
                        || $rule->discount_quantity > $quantity
                    ) {
                        $discountPercent = min(100, $item->discount_percent + $rulePercent);

                        $item->discount_percent = $discountPercent;
                    }

                    break;

                case 'by_fixed':
                    $discountAmount = $quantity * core()->convertPrice($rule->discount_amount);

                    $baseDiscountAmount = $quantity * $rule->discount_amount;

                    break;

                case 'cart_fixed':
                    if ($this->itemTotals[$rule->id]['total_items'] <= 1) {
                        $discountAmount = core()->convertPrice($rule->discount_amount);

                        $baseDiscountAmount = min($item->base_price * $quantity, $rule->discount_amount);
                    } else {
                        $discountRate = $item->base_price * $quantity / $this->itemTotals[$rule->id]['base_total_price'];

                        $maxDiscount = $rule->discount_amount * $discountRate;

                        $discountAmount = core()->convertPrice($maxDiscount);

                        $baseDiscountAmount = min($item->base_price * $quantity, $maxDiscount);
                    }

                    break;

                case 'buy_x_get_y':
                    if (
                        !$rule->discount_step
                        || $rule->discount_amount > $rule->discount_step
                    ) {
                        break;
                    }

                    $buyAndDiscountQty = $rule->discount_step + $rule->discount_amount;

                    $qtyPeriod = floor($quantity / $buyAndDiscountQty);

                    $freeQty = $quantity - $qtyPeriod * $buyAndDiscountQty;

                    $discountQty = $qtyPeriod * $rule->discount_amount;

                    if ($freeQty > $rule->discount_step) {
                        $discountQty += $freeQty - $rule->discount_step;
                    }

                    $discountAmount = $discountQty * $item->price;

                    $baseDiscountAmount = $discountQty * $item->base_price;

                    break;
            }

            $item->discount_amount = min(
                $item->discount_amount + $discountAmount,
                $item->price * $quantity + $item->tax_amount
            );
            $item->base_discount_amount = min(
                $item->base_discount_amount + $baseDiscountAmount,
                $item->base_price * $quantity + $item->base_tax_amount
            );

            $appliedRuleIds[$rule->id] = $rule->id;

            if ($rule->end_other_rules) {
                break;
            }
        }

        $item->applied_cart_rule_ids = implode(',', $appliedRuleIds);

        $item->save();

        return $appliedRuleIds;
    }

    /**
     * Cart shipping discount calculation process
     *
     * @return self|void
     */
    public function processShippingDiscount()
    {
        if (!$selectedShipping = $this->cart->selected_shipping_rate) {
            return;
        }

        $selectedShipping->discount_amount = 0;
        $selectedShipping->base_discount_amount = 0;

        $appliedRuleIds = [];

        foreach ($this->getCartRules() as $rule) {
            if (!$this->canProcessRule($rule)) {
                continue;
            }

            if (!$this->validator->validate($rule, $this->cart)) {
                continue;
            }

            if (
                !$rule
                || !$rule->apply_to_shipping
            ) {
                continue;
            }

            $discountAmount = $baseDiscountAmount = 0;

            switch ($rule->action_type) {
                case 'by_percent':
                    $rulePercent = min(100, $rule->discount_amount);

                    $discountAmount = ($selectedShipping->price - $selectedShipping->discount_amount) * $rulePercent / 100;

                    $baseDiscountAmount = ($selectedShipping->base_price - $selectedShipping->base_discount_amount) * $rulePercent / 100;

                    break;

                case 'by_fixed':
                    $discountAmount = core()->convertPrice($rule->discount_amount);

                    $baseDiscountAmount = $rule->discount_amount;

                    break;
            }

            $selectedShipping->discount_amount = min($selectedShipping->discount_amount + $discountAmount, $selectedShipping->price);

            $selectedShipping->base_discount_amount = min(
                $selectedShipping->base_discount_amount + $baseDiscountAmount,
                $selectedShipping->base_price
            );

            $selectedShipping->save();

            $appliedRuleIds[$rule->id] = $rule->id;

            if ($rule->end_other_rules) {
                break;
            }
        }

        $selectedShipping->save();

        $cartAppliedCartRuleIds = array_merge(explode(',', $this->cart->applied_cart_rule_ids), $appliedRuleIds);

        $cartAppliedCartRuleIds = array_filter($cartAppliedCartRuleIds);

        $cartAppliedCartRuleIds = array_unique($cartAppliedCartRuleIds);

        $this->cart = $this->cartRepository->update([
            'applied_cart_rule_ids' => implode(',', $cartAppliedCartRuleIds),
        ], $this->cart->id);

        return $this;
    }

    /**
     * Cart free shipping discount calculation process
     *
     * @return void
     */
    public function processFreeShippingDiscount()
    {
        if (!$selectedShipping = $this->cart->selected_shipping_rate) {
            return;
        }

        $selectedShipping->discount_amount = 0;

        $selectedShipping->base_discount_amount = 0;

        $appliedRuleIds = [];

        foreach ($this->cart->items->all() as $item) {
            foreach ($this->getCartRules() as $rule) {
                if (!$this->canProcessRule($rule)) {
                    continue;
                }

                /* given CartItem instance to the validator */
                if (!$this->validator->validate($rule, $item)) {
                    continue;
                }

                if (
                    !$rule
                    || !$rule->free_shipping
                ) {
                    continue;
                }

                $selectedShipping->price = 0;

                $selectedShipping->base_price = 0;

                $selectedShipping->save();

                $appliedRuleIds[$rule->id] = $rule->id;

                if ($rule->end_other_rules) {
                    break;
                }
            }
        }

        $cartAppliedCartRuleIds = array_merge(explode(',', $this->cart->applied_cart_rule_ids), $appliedRuleIds);

        $cartAppliedCartRuleIds = array_filter($cartAppliedCartRuleIds);

        $cartAppliedCartRuleIds = array_unique($cartAppliedCartRuleIds);

        $this->cart->applied_cart_rule_ids = implode(',', $cartAppliedCartRuleIds);

        $this->cart = $this->cartRepository->update([
            'applied_cart_rule_ids' => implode(',', $cartAppliedCartRuleIds),
        ], $this->cart->id);
    }

    /**
     * Calculate cart item totals for each rule
     *
     * @return array|void
     */
    public function calculateCartItemTotals()
    {
        foreach ($this->getCartRules() as $rule) {
            if ($rule->action_type != 'cart_fixed') {
                continue;
            }

            $totalPrice = $totalBasePrice = $validCount = 0;

            foreach ($this->cart->items as $item) {
                if (!$this->canProcessRule($rule)) {
                    continue;
                }

                if (!$this->validator->validate($rule, $item)) {
                    continue;
                }

                $quantity = $rule->discount_quantity ? min($item->quantity, $rule->discount_quantity) : $item->quantity;

                $totalBasePrice += $item->base_price * $quantity;

                $validCount++;
            }

            $this->itemTotals[$rule->id] = [
                'base_total_price' => $totalBasePrice,
                'total_items'      => $validCount,
            ];
        }
    }

    /**
     * Check if coupon code is applied or not
     */
    public function checkCouponCode(): bool
    {
        if (!$this->cart->coupon_code) {
            return true;
        }

        // if ($this->cart->base_grand_total <= 5) {
        //     return false;
        // }

        $coupons = $this->cartRuleCouponRepository->where(['code' => $this->cart->coupon_code])->get();

        foreach ($coupons as $coupon) {
            if (in_array($coupon->cart_rule_id, explode(',', $this->cart->applied_cart_rule_ids))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Divide discount amount to children
     *
     * @param  \Webkul\Checkout\Contracts\CartItem  $item
     * @return void
     */
    protected function divideDiscount($item)
    {
        foreach ($item->children as $child) {
            $ratio = $item->base_total != 0 ? $child->base_total / $item->base_total : 0;

            foreach (['discount_amount', 'base_discount_amount'] as $column) {
                if (!$item->{$column}) {
                    continue;
                }

                $child->{$column} = round(($item->{$column} * $ratio), 4);

                $child->save();
            }
        }
    }

    /**
     * @return \Builder
     */
    public function getCartRuleQuery()
    {
        $customerGroup = $this->customerRepository->getCurrentGroup();

        return $this->cartRuleRepository
            ->leftJoin(
                'cart_rule_customer_groups',
                'cart_rules.id',
                '=',
                'cart_rule_customer_groups.cart_rule_id'
            )
            ->leftJoin('cart_rule_channels', 'cart_rules.id', '=', 'cart_rule_channels.cart_rule_id')
            ->where('cart_rule_customer_groups.customer_group_id', $customerGroup->id)
            ->where('cart_rule_channels.channel_id', core()->getCurrentChannel()->id)
            ->where(function ($query) {
                /** @var Builder $query1 */
                $query->where('cart_rules.starts_from', '<=', Carbon::now()->format('Y-m-d H:m:s'))
                    ->orWhereNull('cart_rules.starts_from');
            })
            ->where(function ($query) {
                /** @var Builder $query2 */
                $query->where('cart_rules.ends_till', '>=', Carbon::now()->format('Y-m-d H:m:s'))
                    ->orWhereNull('cart_rules.ends_till');
            })
            ->where('status', 1)
            ->orderBy('sort_order', 'asc');
    }

    /**
     * @return \Builder
     */
    public function getCatalogRuleSkus()
    {
        $customerGroup = $this->customerRepository->getCurrentGroup();

        $catalog_rules =  $this->catalogRuleRepository
            ->leftJoin(
                'catalog_rule_customer_groups',
                'catalog_rules.id',
                '=',
                'catalog_rule_customer_groups.catalog_rule_id'
            )
            ->leftJoin('catalog_rule_channels', 'catalog_rules.id', '=', 'catalog_rule_channels.catalog_rule_id')
            ->where('catalog_rule_customer_groups.customer_group_id', $customerGroup->id)
            ->where('catalog_rule_channels.channel_id', core()->getCurrentChannel()->id)
            ->where(function ($query) {
                /** @var Builder $query1 */
                $query->where('catalog_rules.starts_from', '<=', Carbon::now()->format('Y-m-d H:m:s'))
                    ->orWhereNull('catalog_rules.starts_from');
            })
            ->where(function ($query) {
                /** @var Builder $query2 */
                $query->where('catalog_rules.ends_till', '>=', Carbon::now()->format('Y-m-d H:m:s'))
                    ->orWhereNull('catalog_rules.ends_till');
            })
            ->where('status', 1)
            ->orderBy('sort_order', 'asc')->get();

        foreach ($catalog_rules as $catalog_rule) {
            if ($catalog_rule->conditions != null) {
                foreach ($catalog_rule->conditions as $condition) {
                    if ($condition['attribute'] == "product|sku" && $condition['operator'] == "==") {
                        $skus[] = $condition['value'];
                    }
                }
            }
        }

        return $skus ?? [];
    }

    /**
     * Check if cart rules are available or not for current customer group and channel
     */
    public function haveCartRules(): bool
    {
        return (bool) $this->getCartRuleQuery()->count();
    }
}
