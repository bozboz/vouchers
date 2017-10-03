<?php

namespace Bozboz\Ecommerce\Vouchers\Contracts;

interface VoucherAdminDecorator
{
    public function getColumns($instance);

    public function getLabel($instance);

    public function getFields($instance);
}
