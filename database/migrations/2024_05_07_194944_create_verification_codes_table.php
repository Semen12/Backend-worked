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

        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
             $table->string('type_email');
           // $table->string('model_type');
            $table->string('code');
            $table->string('verification_value');
            $table->string('status')->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};
