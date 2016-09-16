<?php

namespace Bozboz\Ecommerce\Vouchers\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Ecommerce\Vouchers\VoucherAdminDecorator;

class VoucherController extends ModelAdminController
{
    public function __construct(VoucherAdminDecorator $decorator)
    {
        parent::__construct($decorator);
    }
}
