<?php

namespace Bozboz\Ecommerce\Vouchers;

class VoucherPurchasedEvent
{
	public function handle($event)
	{
		$event->item->orderable->increment('current_uses');
	}
}
