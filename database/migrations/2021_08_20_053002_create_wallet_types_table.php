<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWalletTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->nullable();
            $table->string('description', 100)->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->timestamps();
        });

        // insert default wallet
        DB::table('wallet_types')->insert([
            'name' => config('wallet.wallet_type.name'),
            'slug' => config('wallet.wallet_type.slug'),
            'description' => config('wallet.wallet_type.description'),
            'currency_code' => config('wallet.wallet_type.currency_code'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_types');
    }
}
