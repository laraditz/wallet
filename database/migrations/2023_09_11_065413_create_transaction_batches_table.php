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
            $table->ulid('id')->primary();
            $table->string('type', 50);
            $table->json('amounts')->nullable();
            $table->smallInteger('status')->nullable();
            $table->string('status_description')->nullable();
            $table->string('remark')->nullable();
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
        return config('wallet.table_names.transaction_batches');
    }
};
