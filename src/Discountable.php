<?php

namespace Bozboz\Ecommerce\Vouchers;

use Bozboz\Ecommerce\Orders\Item;
use Bozboz\Ecommerce\Vouchers\Contracts\Voucher as VoucherContract;

interface Discountable
{
	public function getDiscountedAmount(VoucherContract $voucher, Item $item);
}
