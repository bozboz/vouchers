<?php

namespace Bozboz\Ecommerce\Vouchers;

use Bozboz\Admin\Base\Model;
use Bozboz\Ecommerce\Vouchers\Contracts\Voucher as Contract;

abstract class Voucher extends Model implements Contract
{
    protected $dates = [
        'start_date',
        'end_date',
    ];

    protected $fillable = [
        'code',
        'description',
        'is_percent',
        'whole_value',
        'current_uses',
        'max_uses',
        'start_date',
        'end_date',
        'min_order_pence',
        'max_order_pence',
        'min_order',
        'max_order',
    ];

    protected $nullable = [
        'description',
        'max_uses',
        'start_date',
        'end_date',
        'min_order_pence',
        'max_order_pence',
    ];

    public function getValidator()
    {
        return new VoucherValidator;
    }

    public function getMaximumCheckoutQuantityAttribute()
    {
        return 1;
    }

    public function getWholeValueAttribute()
    {
        if ($this->is_percent) {
            return $this->value;
        } else {
            return number_format($this->value / 100, 2);
        }
    }

    public function setWholeValueAttribute($value)
    {
        if (!$this->is_percent) {
            $value = str_replace(',', '', $value) * 100;
        }
        $this->attributes['value'] = $value;
    }

    public function getMinOrderAttribute()
    {
        return is_null($this->min_order_pence) ? null : number_format($this->min_order_pence / 100, 2);
    }

    public function setMinOrderAttribute($value)
    {
        $this->attributes['min_order_pence'] = $value > 0 ? str_replace(',', '', $value) * 100 : null;
    }

    public function getMaxOrderAttribute()
    {
        return is_null($this->max_order_pence) ? null : number_format($this->max_order_pence / 100, 2);
    }

    public function setMaxOrderAttribute($value)
    {
        $this->attributes['max_order_pence'] = $value > 0 ? str_replace(',', '', $value) * 100 : null;
    }

    abstract protected function getProductClass();

    public function discountedProducts()
    {
        return $this->belongsToMany($this->getProductClass(),
            'discounted_products', 'voucher_id', 'product_id'
        );
    }

    public function discountExemptProducts()
    {
        return $this->belongsToMany($this->getProductClass(),
            'discount_exempt_products', 'voucher_id', 'product_id'
        );
    }

    /**
     * Determine if product is valid for voucher
     *
     * @param  Bozboz\Ecommerce\Product\Product  $product
     * @return boolean
     */
    public function isProductValid($product)
    {
        if ($this->discountedProducts->count()) {
            return
                $this->discountedProducts->contains($product) ||
                $this->discountedProducts->contains($product->variationOf);
        }

        elseif ($this->discountExemptProducts->count()) {
            return
                ! $this->discountExemptProducts->contains($product) &&
                ! $this->discountExemptProducts->contains($product->variationOf);
        }

        return true;
    }
}
