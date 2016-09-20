<?php

namespace Bozboz\Ecommerce\Vouchers;

use Bozboz\Ecommerce\Orders\Item;

interface Discountable
{
	public function getDiscountedAmount(Voucher $voucher, Item $item);
}
