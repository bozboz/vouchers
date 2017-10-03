<?php

Route::group(['middleware' => 'web', 'prefix' => 'admin', 'namespace' => 'Bozboz\Ecommerce\Vouchers\Http\Controllers\Admin'], function()
{
    Route::resource('vouchers', 'VoucherController', ['except' => ['show']]);
});
