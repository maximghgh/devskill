<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChaptersTable extends Migration
{
    public function up()
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            
            // Внешний ключ для связи с темой
            $table->foreignId('topic_id')->constrained('topics')->onDelete('cascade');
            
            // Заголовок главы – обязательное поле
            $table->string('title');
            
            // Тип главы: video, text, task, terms
            $table->enum('type', ['video','text','task','terms','quiz'])->default('text');
            
            // Общее поле для контента: редакторский вывод (editor)
            // Используется для типов text, task, terms и даже для описания видео
            $table->json('content')->nullable();
            
            // Если тип видео, дополнительно сохраняем ссылку на видео
            $table->string('video_url')->nullable();
            
            // Поле для сортировки глав внутри темы (необязательно)
            $table->integer('order')->default(0);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chapters');
    }
}



