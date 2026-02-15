<?php

namespace App\Http\Controllers;

use App\Models\LessonScore;
use Illuminate\Http\Request;

class LessonScoreController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'chapter_id' => 'required|integer|exists:chapters,id',
            'student_ids' => 'nullable|string',
        ]);

        $query = LessonScore::query()->where('chapter_id', $validated['chapter_id']);

        if ($request->filled('student_ids')) {
            $ids = collect(explode(',', $request->input('student_ids')))
                ->map(fn ($id) => (int) trim($id))
                ->filter(fn ($id) => $id > 0)
                ->values();

            if ($ids->isNotEmpty()) {
                $query->whereIn('user_id', $ids);
            } else {
                return response()->json([]);
            }
        }

        return response()->json($query->get());
    }

    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'chapter_id' => 'required|integer|exists:chapters,id',
            'user_id' => 'required|integer|exists:users,id',
            'teacher_id' => 'nullable|integer|exists:users,id',
            'score' => 'required|integer|min:0|max:100',
        ]);

        $score = LessonScore::updateOrCreate(
            [
                'chapter_id' => $validated['chapter_id'],
                'user_id' => $validated['user_id'],
            ],
            [
                'teacher_id' => $validated['teacher_id'] ?? null,
                'score' => $validated['score'],
            ]
        );

        return response()->json([
            'success' => true,
            'score' => $score,
        ]);
    }
}
