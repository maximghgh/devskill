<?php

namespace App\Http\Controllers;

use App\Models\FinalTest;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\UserChapterProgress;
use Illuminate\Http\JsonResponse;
use App\Models\FinalTestResult;
use Illuminate\Http\Request;

class FinalTestController extends Controller
{
    public function show($courseId, Request $r)
    {
        $userId = $r->input('user_id') ?? auth()->id();

        // 1. Главы, которые реально надо пройти
        $total = Chapter::whereHas('topic', fn($q)=>$q->where('course_id',$courseId))
                        ->where('type','!=','presentation')   // ← если тест хранится отдельно
                        ->count();

        // 2. Сколько пользователь реально прошёл
        $done  = UserChapterProgress::where('user_id',$userId)
            ->whereHas('chapter.topic', fn($q)=>$q->where('course_id',$courseId))
            ->whereHas('chapter', fn($q)=>$q->where('type','!=','presentation'))
            ->count();

        if ($total === 0 || $done < $total) {
            return response()->json([
                'message' => 'chapters_incomplete',
                'total'   => $total,
                'done'    => $done,
            ], 403);
        }

        $test = FinalTest::where('chapter_id',$courseId)->firstOrFail();
        return response()->json($test->only('id','questions','pass_score'));
    }
    public function submit(FinalTest $test, Request $r)
    {
        $userId  = $r->input('user_id') ?? auth()->id();
        $answers = $r->input('answers', []);
        $score   = $this->calcScore($test, $answers);   // % правильных

        $passScore = $test->pass_score ?? 50;           // по умолчанию 50 %

        if ($score >= $passScore) {
            // ♻︎ записываем только если прошёл
            UserFinalTest::updateOrCreate(
                ['user_id'=>$userId, 'final_test_id'=>$test->id],
                ['score'=>$score,    'passed_at'=>now()]
            );

            // сертификат
            Certificate::firstOrCreate(
                ['user_id'=>$userId,'course_id'=>$test->course_id],
                ['issued_at'=>now(),'path'=>null]
            );

            return response()->json([
                'passed' => true,
                'score'  => $score,
            ]);
        }

        // не прошёл — просто возвращаем результат, БД не трогаем
        return response()->json([
            'passed' => false,
            'score'  => $score,
            'message'=> 'not_enough_score'
        ]);
    }
    public function check(Request $request, $courseId)
    {
        $data = $request->validate([
            'user_id'         => 'required|exists:users,id',
            'answers'         => 'required|array',
            'score'           => 'required|integer|min:0|max:100',
            'correct_count'   => 'required|integer|min:0',
            'total_questions' => 'required|integer|min:1',
        ]);

        $result = FinalTestResult::create([
            'user_id'         => $data['user_id'],
            'course_id'       => $courseId,
            'answers'         => $data['answers'],
            'score'           => $data['score'],
            'correct_count'   => $data['correct_count'],
            'total_questions' => $data['total_questions'],
        ]);

        return response()->json([
            'saved'   => true,
            'resultId'=> $result->id,
        ], 201);
    }
    public function results($courseId)
    {
        // 1. число?
        if (! ctype_digit($courseId)) {
            return response()->json(['message'=>'course_id must be integer'], 400);
        }
        $courseId = (int) $courseId;

        // 2. существует ли курс?  (если нужно)
        if (! Course::whereKey($courseId)->exists()) {
            return response()->json(['message'=>'Course not found'], 404);
        }

        // 3. выборка
        $results = FinalTestResult::with('user')
            ->where('course_id', $courseId)
            ->where('score', '>=', 70)
            ->get()
            ->map(fn ($r) => [
                'name'      => $r->user->name,
                'answer_q1' => (array)($r->answers[1] ?? []),
            ]);

        return response()->json($results);
    }
    public function status(Request $r): JsonResponse
    {
        $userId   = $r->input('user_id')   ?? auth()->id();
        $courseId = $r->input('course_id');

        if (! $userId || ! $courseId) {
            return response()->json(['passed' => false], 400);
        }

        $passed = FinalTestResult::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('score', '>=', 50)    // ваш порог
            ->exists();

        return response()->json(['passed' => $passed]);
    }

    public function store(Request $r, int $courseId)
    {
        /* 1. Валидация входящего JSON */
        $data = $r->validate([
            'questions'  => 'required|array',                 // editor.save()
            'pass_score' => 'required|integer|min:1|max:100', // 1‑100 %
            'topic_id'   => 'nullable|integer|exists:topics,id',
        ]);

        /* 2. Создаём или обновляем тест для этого курса */
        $test = FinalTest::updateOrCreate(
            ['chapter_id' => $courseId],                       // ключ поиска
            [
                'topic_id'   => $data['topic_id'] ?? null,    // null, если не передан
                'questions'  => $data['questions'],           // Laravel сам сериализует
                'pass_score' => $data['pass_score'],
            ]
        );

        /* 3. Ответ */
        return response()->json([
            'saved' => true,
            'id'    => $test->id,
        ], 201);
    }
}

