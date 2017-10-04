<?php

namespace Bozboz\Ecommerce\Vouchers;

use Bozboz\Ecommerce\Orders\Item;
use Bozboz\Ecommerce\Orders\Order;
use Bozboz\Ecommerce\Orders\OrderableException;
use Bozboz\Ecommerce\Orders\Orderable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

abstract class OrderableVoucher extends Voucher implements Orderable
{
	public $table = 'vouchers';

    /**
     * Return list of items associated with this orderable
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function items()
    {
        return $this->morphMany(Item::class, 'orderable');
    }

    /**
     * Determine whether Orderable model can have its quantity adjusted
     *
     * @return boolean
     */
    public function canAdjustQuantity()
    {
        return false;
    }

    /**
     * Determine whether Orderable model can be deleted once set
     *
     * @return boolean
     */
    public function canDelete()
    {
        return true;
    }

    /**
      * Validate voucher item, based on current uses, expiry date and order total
      *
      * @param  int  $quantity
      * @param  Bozboz\Ecommerce\Order\Item  $item
      * @param  Bozboz\Ecommerce\Order\Order  $order
      * @throws Bozboz\Ecommerce\Order\Orderable
      * @return void
      */
    public function validate($quantity, Item $item, Order $order)
    {
        $values = [
            'current_uses' => $this->current_uses + 1,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'order_total' => $order->totalPrice()
        ];

        $rules = [
            'start_date' => 'before:' . date('Y-m-d'),
            'end_date' => 'after:' . date('Y-m-d'),
            'order_total' => "numeric|min:$this->min_order_pence" . ($this->max_order_pence ? "|max:$this->max_order_pence" : null),
        ];

        if ($this->max_uses > 0) {
            $rules['current_uses'] = 'numeric|max:' . $this->max_uses;
        }

        $messages = [
            'current_uses' => 'This voucher has exceded the max use',
            'before' => 'This voucher is not available yet',
            'after' => 'This voucher has expired',
            'order_total.min' => sprintf(
                'Your order must be a minimum of %s to use this voucher code',
                format_money($this->min_order_pence)
            ),
            'order_total.max' => sprintf(
                'Your order must be under %s to use this voucher code',
                format_money($this->max_order_pence)
            ),
        ];

        $validation = Validator::make($values, $rules, $messages);

        try {
            if ($validation->fails()) throw new OrderableException($validation);
            $this->calculatePrice($quantity, $order);
        } catch (OrderableException $e) {
            $item->delete();
            throw $e;
        }
    }

    public function getValidationStartDateAttribute()
    {
        return $this->start_date ?: Carbon::today();
    }

    public function getValidationEndDateAttribute()
    {
        return $this->end_date ?: Carbon::tomorrow();
    }

    public function getValidationMinOrderAttribute()
    {
        return intval($this->min_order_pence);
    }

    public function getValidationMaxOrderAttribute()
    {
        return $this->max_order_pence ?: 99999999999;
    }

    protected function orderHasValidProducts($order)
    {
        return ! $order->items->filter(function($item) {
            return $item->orderable
                && $item->orderable instanceof Discountable
                && $this->isProductValid($item->orderable->ticket);
        })->isEmpty();
    }

    /**
     * Calculate price voucher gives off given $order
     *
     * @param  int  $quantity
     * @param  Bozboz\Ecommerce\Order\Order  $order
     * @throws Bozboz\Ecommerce\Order\OrderableException
     * @return int
     */
    public function calculatePrice($quantity, Order $order)
    {
        $orderTotal = $this->calculateOrderTotal($order);

        $this->validateOrderTotal($orderTotal);

        return max($this->getValue($orderTotal), -$orderTotal);
    }

    /**
     * Filter collection of items by voucher's discounted and discount-exempt
     * products
     *
     * @param  Bozboz\Ecommerce\Order\Order  $order
     * @return int
     */
    protected function calculateOrderTotal(Order $order)
    {
        return $order->items->sum(function($item) {
            if ($item->orderable instanceof Discountable) {
                return $item->orderable->getDiscountedAmount($this, $item);
            }
        });
    }

    /**
     * Ensure voucher matches any items
     *
     * @param  int  $orderTotal
     * @throws Bozboz\Ecommerce\Order\OrderableException
     * @return void
     */
    protected function validateOrderTotal($orderTotal)
    {
        $validation = Validator::make(
            ['orderTotal' => $orderTotal],
            ['orderTotal' => 'numeric|min:1'],
            ['min' => 'The voucher is not valid for your order']
        );

        if ($validation->fails()) throw new OrderableException($validation);
    }

    protected function getValue($orderTotal)
    {
        return $this->is_percent ? -($orderTotal * $this->value / 100) : -$this->value;
    }

    /**
     * Calculate weight of item, based on quantity
     *
     * @param  int  $quantity
     * @return float
     */
    public function calculateWeight($quantity)
    {
        return 0;
    }

    /**
     * Consistent label identifier for orderable item
     *
     * @return string
     */
    public function label()
    {
        return "{$this->code} ({$this->description})";
    }

    /**
     * Calculate amount to refund, based on item and quantity
     *
     * @param  Bozboz\Ecommerce\Order\Item  $item
     * @param  int  $quantity
     * @return int
     */
    public function calculateAmountToRefund(Item $item, $quantity)
    {
        return 0;
    }

    /**
     * Determine if orderable is taxable or not
     *
     * @return boolean
     */
    public function isTaxable()
    {
        return ! $this->tax_exempt;
    }

    /**
     * Perform any actions necessary upon successful purchase
     *
     * @param  int $quantity
     * @return void
     */
    public function purchased($quantity)
    {
        $this->current_uses += 1;
        $this->save();
    }

	public function image()
	{
		return '/assets/images/voucher-icon.png';
	}
}
