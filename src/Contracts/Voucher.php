<?php

namespace Bozboz\Ecommerce\Vouchers\Contracts;

interface Voucher
{
    public function getWholeValueAttribute();

    public function setWholeValueAttribute($value);

    public function getMinOrderAttribute();

    public function setMinOrderAttribute($value);

    public function getMaxOrderAttribute();

    public function setMaxOrderAttribute($value);

    public function discountedProducts();

    public function discountExemptProducts();

    public function isProductValid($product);
}
