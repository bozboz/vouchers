<?php

namespace Bozboz\Ecommerce\Vouchers;

use Bozboz\Ecommerce\Orders\Item;

class ProductModifiedInCartEvent
{
	public function handle(Item $item)
	{
		$voucherItems = $item->order->items()
			->with('orderable')
			->where('orderable_type', 'voucher')
			->get();

		foreach($voucherItems as $voucherItem) {
			$voucherItem->updateQuantity($voucherItem->quantity);
			$voucherItem->save();
		}
	}
}
