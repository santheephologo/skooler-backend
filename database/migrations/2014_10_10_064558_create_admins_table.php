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
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('school_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('mobile_no');
            $table->string('address')->nullable();
            $table->json('roles');
            $table->string('profile_pic')->nullable();
            $table->string('password');
            $table->boolean('is_active');
            $table->timestamps();
            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
