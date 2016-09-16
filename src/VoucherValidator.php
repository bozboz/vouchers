<?php

namespace Bozboz\Ecommerce\Vouchers;

use Bozboz\Admin\Services\Validators\Validator;

class VoucherValidator extends Validator
{
	protected $rules = array(
		'code' => 'required',
		'whole_value' => 'required|numeric',
		'max_uses' => 'numeric',
        'start_date' => 'date',
		'end_date' => 'date',
	);
}
