<?php 
// app/Http/Controllers/FinalTestResultController.php
namespace App\Http\Controllers;

use App\Models\FinalTestResult;

class FinalTestResultController extends Controller
{
    /**
     * Вернёт JSON со всеми результатами тестов.
     */
    public function index()
    {
        // Узкие выборки по связям — без утечки PII пользователя и лишних полей курса.
        $results = FinalTestResult::with([
            'user:id,name',
            'course:id,card_title',
        ])->get();

        return response()->json($results);
    }
    public function show($id)
    {
        // 1) Подтягиваем результат вместе с пользователем и курсом
        $result = FinalTestResult::with(['user', 'course'])->findOrFail($id);

        // 2) Получаем сам тест для этого курса
        $finalTest = \App\Models\FinalTest::where('chapter_id', $result->course_id)
                    ->firstOrFail();

        // 3) Собираем ответ
        return response()->json([
            'user'      => $result->user->name,
            'questions' => $finalTest->questions,   // [{id,prompt,options,answer},…]
            'answers'   => $result->answers,        // {1:0,2:[…],…}
        ]);
    }

}
