<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 50);
            $table->json('amounts')->nullable();
            $table->smallInteger('status')->nullable();
            $table->string('status_description')->nullable();
            $table->string('remark')->nullable();
            $table->json('data')->nullable();
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
        Schema::dropIfExists('transaction_batches');
    }
}
