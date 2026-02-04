<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('course_name');
        });

        $courses = DB::table('courses')
            ->select('id', 'course_name')
            ->orderBy('id')
            ->get();

        $used = [];

        foreach ($courses as $course) {
            $name = $course->course_name ?? '';
            $base = Str::slug($name, '-', 'ru');
            if ($base === '') {
                $map = [
                    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
                    'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
                    'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
                    'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
                    'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
                    'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu',
                    'я' => 'ya', 'ъ' => '', 'ь' => '',
                ];
                $lower = mb_strtolower($name, 'UTF-8');
                $base = Str::slug(strtr($lower, $map), '-');
            }
            if ($base === '') {
                $base = 'course-' . $course->id;
            }

            $slug = $base;
            $i = 2;
            while (in_array($slug, $used, true)) {
                $slug = $base . '-' . $i;
                $i++;
            }

            $used[] = $slug;

            DB::table('courses')
                ->where('id', $course->id)
                ->update(['slug' => $slug]);
        }
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
