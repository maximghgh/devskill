<?php

namespace App\Http\Controllers;

use App\Models\FinalTest;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
// class FinalTestController extends Controller
// {
//     public function show(Course $course)
//     {
//         // получаем вопросы
//         $test = FinalTest::where('chapter_id', $course->id)->firstOrFail();

//         return view('final-test', [
//         'course'               => $course,
//         'initialFinalTestData' => $test->questions,
//         ]);
//     }
//     public function showJson(Course $course): JsonResponse
//     {
//         // Подбираем запись из final_tests по course_id
//         $test = FinalTest::where('chapter_id', $course->id)->first();

//         if (! $test) {
//             return response()->json([
//                 'message' => 'Test not found'
//             ], 404);
//         }

//         return response()->json([
//             'course'    => $course->only(['id','title']),
//             'questions' => $test->questions,   // ожидается, что это массив JSON
//         ]);
//     }
// }
class FinalTestController extends Controller
{
    /**
     * Вернуть финальный тест + инфо о курсе по ID курса.
     *
     * URL: /api/final-tests/{course}
     *
     * @param  int  $course   ID из таблицы courses (он же хранится в column chapter_id)
     * @return JsonResponse
     */
    public function show(int $course): JsonResponse
    {
        $course = Course::findOrFail($course);
        $test   = $course->finalTest()->firstOrFail();

        return response()->json([
            'course' => [
                'id'    => $course->id,
                'title' => $course->card_title,
            ],
            'test'   => $test->questions,   // ⇦ сырой Editor JS JSON-объект
        ]);
    }
}

