<?php

namespace Bozboz\Ecommerce\Vouchers;

use Bozboz\Ecommerce\Orders\Item;
use Bozboz\Ecommerce\Vouchers\Contracts\Voucher as VoucherContract;

trait Discounted
{
    public function getDiscountedAmount(VoucherContract $voucher, Item $item)
    {
        if ( ! $voucher->isProductValid($this)) {
            return 0;
        }
        return $item->total_price_pence_ex_vat;
    }
}
