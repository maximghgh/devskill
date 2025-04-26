<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); 
            $table->string('email')->unique(); 
            $table->date('birthday')->nullable();
            // Поле role для статуса: 1 - ученик, 2 - преподаватель, 3 - администратор
            $table->unsignedTinyInteger('role')->default(1);
            $table->string('position')->nullable()->after('role');
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('password');
            $table->timestamps(); // created_at и updated_at
            $table->string('photo')->nullable()->after('password');
        });
    }

    public function down() {
        Schema::dropIfExists('users');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
