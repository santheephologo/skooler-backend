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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('school_id');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->unsignedBigInteger('student_id');
            $table->string('mobile_no');
            $table->string('email');
            $table->json('address')->nullable();
            $table->string('password');
            $table->string('profile_pic')->nullable();
            $table->boolean('is_active');
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
