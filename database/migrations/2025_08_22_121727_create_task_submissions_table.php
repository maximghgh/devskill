<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_submissions', function (Blueprint $table) {
            $table->id();

            // Кто отправил
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Где отправил
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();

            // Текст от студента и преподавателя
            $table->text('comment')->nullable();           // комментарий пользователя
            $table->text('teacher_comment')->nullable();   // комментарий преподавателя

            // Файл
            $table->string('file_path')->nullable();       // storage path (storage/app/public/...)

            // Статус проверки
            // pending (в очереди), in_review (на проверке), approved (зачтено), rejected (не зачтено), graded (оценено баллом)
            $table->enum('status', ['pending','in_review','approved','rejected','graded'])->default('pending');

            // Оценка (может быть пустой — тогда используем approved/rejected)
            $table->unsignedSmallInteger('grade')->nullable(); // например 0..100

            // Кто проверил (опционально)
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();

            // Номер попытки (на всякий случай, если разрешишь несколько отправок)
            $table->unsignedInteger('attempt')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'course_id', 'chapter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_submissions');
    }
};
