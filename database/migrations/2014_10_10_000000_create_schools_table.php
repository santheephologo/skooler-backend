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
        Schema::create('schools', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('address');
            $table->string('country');
            $table->string('country_code');
            $table->string('currency');
            $table->string('phone');
            $table->string('email');
            $table->json('ui');
            $table->boolean('is_active')->default(true);
            $table->dateTime('subscription_expiry');
            $table->boolean('delivery');
            $table->boolean('pickup');
            $table->string('logo');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
