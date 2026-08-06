<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Раньше группе задавалось число «открыто первых N тем» (groups.open_topics_count),
     * и настройка жила в админке. Теперь преподаватель отмечает произвольный набор тем
     * на странице курса, поэтому связь становится многие-ко-многим.
     */
    public function up(): void
    {
        Schema::create('group_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['group_id', 'topic_id']);
        });

        $this->convertOpenTopicsCount();

        if (Schema::hasColumn('groups', 'open_topics_count')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropColumn('open_topics_count');
            });
        }
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedInteger('open_topics_count')->default(0)->after('students_count');
        });

        // Откат приблизительный: восстанавливаем количество открытых тем, а не их набор —
        // произвольный набор обратно в «первые N» не сворачивается.
        foreach (DB::table('groups')->select('id')->get() as $group) {
            DB::table('groups')
                ->where('id', $group->id)
                ->update([
                    'open_topics_count' => DB::table('group_topics')
                        ->where('group_id', $group->id)
                        ->count(),
                ]);
        }

        Schema::dropIfExists('group_topics');
    }

    /**
     * Переносим старое значение: «открыто N» -> первые N тем курса по порядку.
     */
    private function convertOpenTopicsCount(): void
    {
        if (!Schema::hasColumn('groups', 'open_topics_count')) {
            return;
        }

        $now = now();
        $rows = [];

        $groups = DB::table('groups')
            ->select('id', 'course_id', 'open_topics_count')
            ->where('open_topics_count', '>', 0)
            ->get();

        foreach ($groups as $group) {
            $topicIds = DB::table('topics')
                ->where('course_id', $group->course_id)
                ->orderBy('order')
                ->orderBy('id')
                ->limit((int) $group->open_topics_count)
                ->pluck('id');

            foreach ($topicIds as $topicId) {
                $rows[] = [
                    'group_id' => $group->id,
                    'topic_id' => $topicId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('group_topics')->insert($chunk);
        }
    }
};
