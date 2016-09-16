<?php

namespace Bozboz\Ecommerce\Vouchers;

use Bozboz\Ecommerce\Order\Item;

interface Discountable
{
	public function getDiscountedAmount(Voucher $voucher, Item $item);
}
