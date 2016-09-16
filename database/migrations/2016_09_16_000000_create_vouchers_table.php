<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVouchersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function(Blueprint $table)
        {
            $table->increments('id');

            $table->string('code', 64);
            $table->string('description')->nullable();
            $table->boolean('is_percent');
            $table->unsignedInteger('value');
            $table->unsignedInteger('current_uses');
            $table->unsignedInteger('max_uses')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->unsignedInteger('min_order_pence')->nullable();
            $table->unsignedInteger('max_order_pence')->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vouchers');
    }

}
