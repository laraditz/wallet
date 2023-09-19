<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laraditz\Wallet\Models\WalletType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->nullable();
            $table->string('description', 100)->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->string('currency_symbol', 10)->nullable();
            $table->smallInteger('code_placement')->nullable();
            $table->smallInteger('symbol_placement')->nullable();
            $table->smallInteger('default_scale')->default(0);
            $table->string('decimal_separator', 5)->nullable();
            $table->string('thousand_separator', 5)->nullable();
            $table->smallInteger('status')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->getTableName());
    }

    private function getTableName()
    {
        return config('wallet.table_names.wallet_types');
    }
};
