<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Запускает миграцию.
     */
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            // Храним e-mail пользователя, по которому будем искать запрос сброса
            $table->string('email')->index();
            
            // Храним код сброса (например, 6-значный код или любой другой токен)
            $table->string('token');
            
            // Храним дату и время создания записи, чтобы можно было определить, не истёк ли срок действия кода
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Откатывает миграцию.
     */
    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
};
