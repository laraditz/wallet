<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $decimal = config('wallet.decimal');

        Schema::create('transactions', function (Blueprint $table) use ($decimal) {
            $table->id();
            $table->uuid('batch_id');
            $table->string('ref_no', 100);
            $table->bigInteger('wallet_id');
            $table->bigInteger('wallet_type_id');
            $table->string('type', 50);
            $table->morphs('model');
            $table->smallInteger('direction');
            $table->string('currency_code', 10)->nullable();
            $table->decimal('amount', 30, $decimal)->nullable();
            $table->decimal('amount_before', 30, $decimal)->nullable();
            $table->decimal('amount_after', 30, $decimal)->nullable();
            $table->string('description', 100)->nullable();
            $table->json('data')->nullable();
            $table->smallInteger('status')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
