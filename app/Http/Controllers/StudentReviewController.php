<?php

namespace App\Http\Controllers;

use App\Models\StudentReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Отзывы преподавателя об учениках (колонка «Отзыв от преподавателя» в журнале).
 */
class StudentReviewController extends Controller
{
    /**
     * Отзывы по курсу — при необходимости только для перечисленных учеников.
     * Используется журналом, чтобы заполнить колонку одним запросом.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'student_ids' => 'nullable|string',
        ]);

        $query = StudentReview::where('course_id', $validated['course_id']);

        if (!empty($validated['student_ids'])) {
            $ids = collect(explode(',', $validated['student_ids']))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->all();

            $query->whereIn('user_id', $ids);
        }

        return response()->json(
            $query->get(['id', 'user_id', 'course_id', 'teacher_id', 'review', 'updated_at'])
        );
    }

    /**
     * Создать или обновить отзыв об ученике по курсу.
     */
    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'course_id' => 'required|integer|exists:courses,id',
            'teacher_id' => 'nullable|integer|exists:users,id',
            'review' => 'nullable|string|max:5000',
        ]);

        $review = StudentReview::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'course_id' => $validated['course_id'],
            ],
            [
                'teacher_id' => $validated['teacher_id'] ?? null,
                'review' => $validated['review'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'review' => $review,
        ]);
    }
}
