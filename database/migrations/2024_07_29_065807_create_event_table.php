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
        Schema::create('event', function (Blueprint $table) {
            $table->id();
            $table->string('school_id');
            $table->string('event_name');
            $table->string('event_info');
            $table->string('venue');
            $table->integer('capacity')->nullable();
            $table->integer('reserved_slots')->nullable();
            $table->decimal('payment', 10, 2)->nullable();
            $table->dateTime('event_datetime');
            $table->dateTime('payment_deadline')->nullable();
            $table->foreign('school_id')->references('id')->on('schools');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};
