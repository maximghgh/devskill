<?php

namespace App\Http\Controllers;

use App\Models\TaskSubmission;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TaskSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $userId = auth()->id() ?? $request->input('user_id');

        $request->validate([
            'chapter_id' => ['required','integer','exists:chapters,id'],
            'message'    => ['nullable','string','max:20000'],
            'file'       => ['nullable','file','max:102400'],
        ]);

        if (!$userId) {
            return response()->json(['ok'=>false,'message'=>'user_id не определён'], 422);
        }

        // Тянем тему и сам курс с полем teachers
        $chapter = Chapter::with([
            'topic:id,course_id',
            'topic.course:id,teachers'
        ])->findOrFail($request->chapter_id);

        $course   = $chapter->topic?->course;
        $courseId = $chapter->topic?->course_id;

        if (!$courseId) {
            return response()->json(['ok'=>false,'message'=>'topic.course_id пуст — не могу определить course_id'], 422);
        }

        // Выбираем первого преподавателя из массива teachers
        $teacherId = null;
        if ($course && is_array($course->teachers) && count($course->teachers) > 0) {
            $candidate = $course->teachers[0];

            // подстрахуемся: такой пользователь существует?
            if (User::whereKey($candidate)->exists()) {
                $teacherId = (int) $candidate;
            }
        }

        // Номер попытки
        $attempt = (TaskSubmission::where('user_id',$userId)
            ->where('chapter_id',$chapter->id)
            ->max('attempt') ?? 0) + 1;

        // Файл
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store("submissions/{$userId}", 'public');
        }

        // Создаём запись (teacher_id может быть null — это ок)
        $submission = TaskSubmission::create([
            'user_id'    => $userId,
            'course_id'  => $courseId,
            'chapter_id' => $chapter->id,
            'comment'    => $request->input('message'),
            'file_path'  => $filePath,
            'status'     => 'pending',
            'attempt'    => $attempt,
            'teacher_id' => $teacherId, // <— вот это мы и добавили
        ]);

        return response()->json([
            'ok'         => true,
            'submission' => $submission,
            'file_url'   => $filePath ? Storage::disk('public')->url($filePath) : null,
        ], 201);
    }

    public function getByStudent($courseId, $studentId)
    {
        $student = User::findOrFail($studentId);

        $submissions = TaskSubmission::with(['chapter:id,title'])   // 👈 подтягиваем title
            ->where('course_id', $courseId)
            ->where('user_id', $studentId)
            ->get()
            ->map(function ($s) {
                $s->file_url = $s->file_path ? Storage::disk('public')->url($s->file_path) : null;
                return $s;
            });

        return response()->json([
            'student'     => $student,
            'submissions' => $submissions,
        ]);
    }

    public function update(Request $request, $id)
    {
        $submission = TaskSubmission::findOrFail($id);

        $submission->update([
            'teacher_comment' => $request->input('teacher_comment'),
            'status' => $request->input('status'),
            'grade' => $request->input('grade'),
        ]);

        return response()->json(['ok' => true, 'submission' => $submission]);
    }
    public function mySubmission(Chapter $chapter, Request $request)
    {
        $userId = (int) $request->query('user_id');   // из query ?user_id=...
        if (!$userId) {
            return response()->json(['submission' => null]);
        }

        $submission = TaskSubmission::with('chapter:id,title')
            ->where('chapter_id', $chapter->id)
            ->where('user_id', $userId)
            ->latest('id')
            ->first();

        if ($submission && $submission->file_path) {
            $submission->file_url = Storage::disk('public')->url($submission->file_path);
        }

        return response()->json(['submission' => $submission]);
    }
}
