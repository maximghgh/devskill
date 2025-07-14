<?php

namespace App\Http\Controllers;

use App\Models\FinalTest;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\UserChapterProgress;
use Illuminate\Http\JsonResponse;
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
        $userId = $r->user_id ?? auth()->id();
        $answers = $r->input('answers',[]);          // [qId=>index]
        $score   = $this->calcScore($test,$answers); // → %
        UserFinalTest::updateOrCreate(
            ['user_id'=>$userId,'final_test_id'=>$test->id],
            ['score'=>$score,'passed_at'=>now()]
        );

        // если прошёл ‒ выдаём сертификат
        if ($score >= $test->pass_score) {
            Certificate::firstOrCreate(
                ['user_id'=>$userId,'course_id'=>$test->course_id],
                ['issued_at'=>now(),'path'=>null]   // path заполняете при генерации PDF
            );
        }
        return response()->json(['score'=>$score]);
    }

}

