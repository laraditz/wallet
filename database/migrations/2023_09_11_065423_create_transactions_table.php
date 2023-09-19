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
            $table->foreignUlid('batch_id');
            $table->string('ref_no', 100);
            $table->foreignId('wallet_id')->constrained()->restrictOnDelete();
            $table->foreignId('wallet_type_id')->constrained()->restrictOnDelete();
            $table->string('type', 50);
            $table->morphs('model');
            $table->smallInteger('direction');
            $table->string('currency_code', 10)->nullable();
            $table->bigInteger('amount')->nullable();
            $table->bigInteger('amount_before')->nullable();
            $table->bigInteger('amount_after')->nullable();
            $table->string('description', 100)->nullable();
            $table->json('metadata')->nullable();
            $table->smallInteger('status')->nullable();
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
        return config('wallet.table_names.transactions');
    }
};
