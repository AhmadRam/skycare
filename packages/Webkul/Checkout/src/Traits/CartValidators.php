<?php

namespace Webkul\Checkout\Traits;

/**
 * Cart validators. In this trait, you will get all sorted collections of
 * methods which can be used to check the carts for validation.
 *
 * Note: This trait will only work with the Cart facade. Unless and until,
 * you have all the required repositories in the parent class.
 */
trait CartValidators
{
    /**
     * Check whether cart has product.
     *
     * @param  \Webkul\Product\Models\Product  $product
     */
    public function hasProduct($product): bool
    {
        $cart = $this->getCart();

        if (! $cart) {
            return false;
        }

        $count = $cart->all_items()->where('product_id', $product->id)->count();

        return $count > 0;
    }

    /**
     * Checks if cart has any error.
     */
    public function hasError(): bool
    {
        if (
            ! $this->getCart()
            || ! $this->isItemsHaveSufficientQuantity()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if all cart items have sufficient quantity.
     */
    public function isItemsHaveSufficientQuantity(): bool
    {
        $cart = cart()->getCart();

        if (! $cart) {
            return false;
        }

        foreach ($cart->items as $item) {
            if (! $this->isItemHaveQuantity($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if all cart items have sufficient quantity.
     *
     * @param  \Webkul\Checkout\Contracts\CartItem  $item
     */
    public function isItemHaveQuantity($item): bool
    {
        return $item->getTypeInstance()->isItemHaveQuantity($item);
    }

    /**
     * Check minimum order.
     */
    public function checkMinimumOrder(): bool
    {
        $cart = $this->getCart();

        if (! $cart) {
            return false;
        }

        return $cart->checkMinimumOrder();
    }


    /**
     * Check minimum Order Amount of cart.
     */
    public function getOrderAmount(): int
    {
        $minimumOrderAmount = $this->cart->sub_total;

        if (core()->getConfigData('sales.order_settings.minimum_order.include_tax_to_amount')) {
            $minimumOrderAmount += $this->cart->tax_total;
        }

        if (core()->getConfigData('sales.order_settings.minimum_order.include_discount_amount')) {
            $minimumOrderAmount -= $this->cart->tax_total;
        }

        return $minimumOrderAmount;
    }

    /**
     * Check minimum order.
     */
    public function haveMinimumOrderAmount(): bool
    {
        if (! core()->getConfigData('sales.order_settings.minimum_order.enable')) {
            return true;
        }

        return $this->getOrderAmount() >= ((int) core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0);
    }
}
