<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->json('editor_data')->nullable(); // Данные EditorJS
            $table->string('image')->nullable(); // Путь или URL изображения
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('news');
    }
}

