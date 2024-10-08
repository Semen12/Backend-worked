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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID пользователя из таблицы Users
            $table->text('name'); // Название
            $table->text('type'); // Тип учетной записи, например,  приложение и т.д.
            $table->text('url')->nullable(); // Источник учетной записи (URL)
            $table->text('login'); // Логин
            $table->text('password')->nullable(); // Пароль от учетной записи
            $table->text('description')->nullable(); // Комментарий (если указан)
            $table->timestamps(); // Дата создания и обновления

            // Внешний ключ для связи с таблицей Users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
