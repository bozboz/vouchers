<?php

namespace Bozboz\Ecommerce\Vouchers;

use Bozboz\Ecommerce\Orders\Item;

trait Discounted
{
    public function getDiscountedAmount(Voucher $voucher, Item $item)
    {
        if ($voucher->isProductValid($this)) {
            return $item->total_price_pence_ex_vat;
        }
    }
}