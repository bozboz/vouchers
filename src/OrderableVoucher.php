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

	public function getDates()
	{
		return array('created_at', 'updated_at', 'expiry_date');
	}

	public function items()
	{
		return $this->morphMany('Bozboz\Ecommerce\Orders\Item', 'orderable');
	}

	public function canAdjustQuantity()
	{
		return false;
	}

	public function canDelete()
	{
		return true;
	}

	public function calculateAmountToRefund(Item $item, $quantity)
	{
		return 0;
	}

	public function isTaxable()
	{
		return false;
	}

	public function purchased($quantity)
	{
		$this->increment('current_uses');
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
		$values = array(
			'current_uses' => $this->current_uses + 1,
			'start_date' => $this->start_date,
			'end_date' => $this->end_date,
			'order_total' => $order->totalPrice()
		);

		$rules = array(
			'start_date' => 'before:' . date('Y-m-d'),
			'end_date' => 'after:' . date('Y-m-d'),
			'order_total' => "numeric|min:$this->min_order_pence" . ($this->max_order_pence ? "|max:$this->max_order_pence" : null),
		);

		if ($this->max_uses > 0) {
			$rules['current_uses'] = 'numeric|max:' . $this->max_uses;
		}

		$messages = array(
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
		);

		$validation = Validator::make($values, $rules, $messages);

		try {
			if ($validation->fails()) throw new OrderableException($validation);
			$this->calculatePrice($quantity, $order);
		} catch (OrderableException $e) {
			$item->delete();
			throw $e;
		}
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

	public function calculateWeight($quantity)
	{
		return 0;
	}

	public function calculateTax(Item $item, $taxAmount)
	{

	}

	public function zeroTax(Item $item, $taxAmount)
	{

	}

	public function label()
	{
		return sprintf('%s (%s)', $this->description, $this->code);
	}

	public function detailedLabel()
	{
		return $this->label();
	}

	protected function getValue($orderTotal)
	{
		return $this->is_percent ? -($orderTotal * $this->value / 100) : -$this->value;
	}

	public function image()
	{
		return '/assets/images/voucher-icon.png';
	}
}
