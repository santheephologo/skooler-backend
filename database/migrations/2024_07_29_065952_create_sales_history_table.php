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
        Schema::create('sales_history', function (Blueprint $table) {
            $table->id();
            $table->string('school_id');
            $table->unsignedBigInteger('user_id');
            $table->string('order_type');
            $table->json('products');
            $table->decimal('total_price', 15, 2);
            $table->string('payment_method');
            $table->string('bank_slip')->nullable();
            $table->string('order_status');
            $table->dateTime('dispatch_datetime')->nullable();
            $table->longText('dispatch_address')->nullable();
            $table->boolean('reviewed');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_history');
    }
};
