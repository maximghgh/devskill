<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // Сколько тем (по порядку) открыто для этой группы.
            // Разные группы одного курса могут иметь разное число открытых тем.
            $table->unsignedInteger('open_topics_count')->default(0)->after('students_count');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('open_topics_count');
        });
    }
};
