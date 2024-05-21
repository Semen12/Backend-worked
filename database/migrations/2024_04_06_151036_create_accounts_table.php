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
            $table->unsignedInteger('user_id'); // ID пользователя из таблицы Users
            $table->string('name'); // Название
            $table->string('type'); // Тип учетной записи например ссылка, приложение и т.д.
            $table->string('url')->nullable(); // Источник учетной записи (URL)
            $table->string('login'); // Логин
            $table->string('password')->nullable(); // Хэш пароля (если указан)
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
