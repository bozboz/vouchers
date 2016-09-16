<?php

namespace Bozboz\Ecommerce\Vouchers\Providers;

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
    }
}
