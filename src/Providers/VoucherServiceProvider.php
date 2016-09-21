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

        $this->app['events']->listen(
            'cart.item.updated: *',
            ProductModifiedInCartEvent::class
        );

        $this->app['events']->listen(
            'cart.item.removed: *',
            ProductModifiedInCartEvent::class
        );

        $this->app['events']->listen(
            'admin.renderMenu',
            function($menu)
            {
                if ($menu->gate('ecommerce')) {
                    $menu[$this->app['translator']->get('ecommerce::ecommerce.menu_name')] = [
                        'Vouchers' => $this->app['url']->route('admin.vouchers.index'),
                    ];
                }
            }
        );
    }
}
