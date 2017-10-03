<?php

namespace Bozboz\Ecommerce\Vouchers;

use Bozboz\Admin\Base\Model;
use Bozboz\Ecommerce\Orders\Item;
use Bozboz\Ecommerce\Orders\Order;
use Bozboz\Ecommerce\Orders\Orderable;
use Bozboz\Ecommerce\Products\Product;
use Bozboz\Ecommerce\Vouchers\Contracts\Voucher as Contract;

abstract class Voucher extends Model implements Contract, Orderable
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

    /**
     * Return list of items associated with this orderable
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function items()
    {
        return $this->morphMany(Item::class, 'orderable');
    }

    /**
     * Determine whether Orderable model can have its quantity adjusted
     *
     * @return boolean
     */
    public function canAdjustQuantity()
    {
        return false;
    }

    /**
     * Determine whether Orderable model can be deleted once set
     *
     * @return boolean
     */
    public function canDelete()
    {
        return true;
    }

    /**
     * Validate item in context of order and quantity requested
     *
     * @param  int  $quantity
     * @param  Bozboz\Ecommerce\Order\Order  $order
     * @return void
     */
    public function validate($quantity, Item $item, Order $order)
    {
        # TODO
        return true;
    }

    /**
     * Calculate price of item, based on quantity and order parameters
     *
     * @param  int  $quantity
     * @param  Bozboz\Ecommerce\Order\Order  $order
     * @return float
     */
    public function calculatePrice($quantity, Order $order)
    {
        if ($this->is_percent) {
            return ($order->totalPrice() * -1) + ($order->totalPrice() / 100 * $this->value);
        }
        return $this->value * -1;
    }

    /**
     * Calculate weight of item, based on quantity
     *
     * @param  int  $quantity
     * @return float
     */
    public function calculateWeight($quantity)
    {
        return 0;
    }

    /**
     * Consistent label identifier for orderable item
     *
     * @return string
     */
    public function label()
    {
        return "{$this->code} ({$this->description})";
    }

    /**
     * Get path to an image representing the orderable item
     *
     * @return string
     */
    public function image()
    {
        return '';
    }

    /**
     * Calculate amount to refund, based on item and quantity
     *
     * @param  Bozboz\Ecommerce\Order\Item  $item
     * @param  int  $quantity
     * @return int
     */
    public function calculateAmountToRefund(Item $item, $quantity)
    {
        throw new \Exception('Method not implemented');
    }

    /**
     * Determine if orderable is taxable or not
     *
     * @return boolean
     */
    public function isTaxable()
    {
        return false;
    }

    /**
     * Perform any actions necessary upon successful purchase
     *
     * @param  int $quantity
     * @return void
     */
    public function purchased($quantity)
    {
        $this->current_uses += 1;
        $this->save();
    }
}
