<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->getTableName(), function (Blueprint $table) {
            $table->id();
            $table->string('address')->nullable();
            $table->foreignId('wallet_type_id')->constrained()->restrictOnDelete();
            $table->morphs('model');
            $table->bigInteger('available_amount')->default(0);
            $table->bigInteger('balance_amount')->default(0);
            $table->bigInteger('accumulated_amount')->default(0);
            $table->bigInteger('used_amount')->default(0);
            $table->smallInteger('status')->nullable();
            $table->bigInteger('description')->nullable();
            $table->json('metadata')->nullable();
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
        return config('wallet.table_names.wallets');
    }
};
