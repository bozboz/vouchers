<?php

namespace Bozboz\Ecommerce\Vouchers;


use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\BelongsToManyField;
use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Admin\Fields\DateTimeField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Fields\TextareaField;
use Bozboz\Ecommerce\Product\ProductDecorator;
use Bozboz\Ecommerce\Products\Pricing\PriceField;

class VoucherAdminDecorator extends ModelAdminDecorator
{
	public function __construct(Voucher $voucher)
	{
		parent::__construct($voucher);
	}

	public function getColumns($instance)
	{
		return [
			'Name' => $instance ? $this->getLabel($instance) : null,
			'Value' => $instance->is_percent ? $instance->value . '%' : format_money($instance->value),
			'Usage' => sprintf('%d/%s', $instance->current_uses, $instance->max_uses ? $instance->max_uses : '∞'),
			'Expires' => $instance->expiry_date ? $instance->expiry_date->diffForHumans() : 'never',
			'Min. Order' => $instance->min_order ? format_money($instance->min_order) : '-'
		];
	}

	public function getLabel($instance)
	{
		return sprintf('%s (%s)', $instance->code, $instance->description);
	}

	public function getFields($instance)
	{
		return [
			new TextField('code'),
			new TextareaField('description'),
			new CheckboxField('is_percent'),
			new TextField('whole_value', ['label' => 'Value']),
			new TextField('max_uses'),
			new TextField('current_uses', ['disabled']),
			new DateTimeField('start_date'),
			new DateTimeField('end_date'),
			new PriceField('min_order'),
			new PriceField('max_order'),
			new BelongsToManyField($this, $instance->discountedProducts(), []),
			new BelongsToManyField($this, $instance->discountExemptProducts(), []),
		];
	}

	public function getSyncRelations()
	{
		return [/*'discountedProducts', 'discountExemptProducts'*/];
	}
}
