<?php

namespace Bozboz\Ecommerce\Vouchers\Providers;

use Bozboz\Ecommerce\Vouchers\ProductModifiedInCartEvent;
use Illuminate\Support\ServiceProvider;

class VoucherServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        $packageRoot = __DIR__ . '/../..';

        $this->publishes([
            "$packageRoot/database/migrations/" => database_path('migrations')
        ], 'migrations');

        require "$packageRoot/src/Http/routes.php";

        $this->app['events']->listen(
            'cart.item.updated: *',
            ProductModifiedInCartEvent::class
        );

        $this->app['events']->listen(
            'cart.item.removed: *',
            ProductModifiedInCartEvent::class
        );
    }
}
