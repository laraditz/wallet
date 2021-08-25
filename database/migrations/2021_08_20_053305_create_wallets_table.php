<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $decimal = config('wallet.decimal');

        Schema::create('wallets', function (Blueprint $table) use ($decimal) {
            $table->id();
            $table->integer('wallet_type_id');
            $table->morphs('model');
            $table->decimal('available_amount', 30, $decimal)->default(0);
            $table->decimal('balance_amount', 30, $decimal)->default(0);
            $table->decimal('accumulated_amount', 30, $decimal)->default(0);
            $table->decimal('used_amount', 30, $decimal)->default(0);
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
        Schema::dropIfExists('wallets');
    }
}
