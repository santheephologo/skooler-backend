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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('school_id');
            $table->unsignedBigInteger('event_id');
            $table->string('event_name');
            $table->unsignedBigInteger('user_id');
            $table->integer('tickets');
            $table->decimal('paid', 10, 2);
            $table->string('payment_method');
            $table->string('bank_slip')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->foreign('event_id')->references('id')->on('event');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
