<?php

namespace Bozboz\Ecommerce\Vouchers\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Ecommerce\Vouchers\Contracts\VoucherAdminDecorator;

class VoucherController extends ModelAdminController
{
    public function __construct(VoucherAdminDecorator $decorator)
    {
        parent::__construct($decorator);
    }

    public function viewPermissions($stack)
    {
        $stack->add('ecommerce');
    }

    public function createPermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }

    public function editPermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }

    public function deletePermissions($stack, $instance)
    {
        $stack->add('ecommerce', $instance);
    }
}
