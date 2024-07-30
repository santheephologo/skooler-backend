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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('school_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("product_id");
            $table->string('product_name');
            $table->integer('qty');
            $table->string('type');
            $table->string("description");
            $table->string("status");
            $table->json('images')->nullable();
            $table->foreign('order_id')->references('id')->on('sales_history');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
