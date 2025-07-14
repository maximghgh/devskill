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
            
            $table->enum('type', ['video','text','task','terms','presentation'])->default('text');

            $table->string('video_url')->nullable();
            $table->string('presentation_path')->nullable();
            $table->json('content')->nullable();
            
            // Поле для сортировки глав внутри темы (необязательно)
            $table->integer('order')->default(0);

            $table->string('correct_answer')->nullable();
            $table->integer('points')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chapters');
    }
}



