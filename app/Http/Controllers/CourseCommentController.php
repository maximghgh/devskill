<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseComment;
use Illuminate\Http\Request;

class CourseCommentController extends Controller
{
    // Получение комментариев для курса
    public function index($courseId)
    {
        $course = Course::findOrFail($courseId);
        // Загружаем комментарии верхнего уровня с вложенными ответами
        $comments = CourseComment::where('course_id', $courseId)
            ->whereNull('parent_id')
            ->with('children', 'user')
            ->get();

        return response()->json($comments);
    }

    // Создание нового комментария
    public function store(Request $request, $courseId)
    {
        $validated = $request->validate([
            'body'      => 'required|string',
            'parent_id' => 'nullable|exists:course_comments,id',
            'user_id'   => 'nullable|integer',
            'user_name' => 'nullable|string',
        ]);

        $validated['course_id'] = $courseId;
        // Если авторизованный пользователь, можно установить user_id
        // $validated['user_id'] = auth()->id();

        $comment = CourseComment::create($validated);

        return response()->json($comment, 201);
    }

    // Обновление комментария (если редактирование необходимо)
    public function update(Request $request, CourseComment $comment)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $comment->update($validated);

        return response()->json($comment);
    }

    // Удаление комментария
    public function destroy(CourseComment $comment)
    {
        $comment->delete();
        return response()->json(['success' => true]);
    }
    public function likeComment($courseId, $commentId)
    {
        // Найти курс (убедиться, что он существует)
        $course = Course::findOrFail($courseId);
        // Найти комментарий
        $comment = CourseComment::where('course_id', $courseId)->findOrFail($commentId);

        // Наивный вариант: просто увеличиваем likes на 1
        // (можно добавить проверки, кто лайкнул и т.д.)
        $comment->increment('likes');
        
        return response()->json([
            'success' => true,
            'likes' => $comment->likes,
            'dislikes' => $comment->dislikes,
        ]);
    }

    public function dislikeComment($courseId, $commentId)
    {
        $course = Course::findOrFail($courseId);
        $comment = CourseComment::where('course_id', $courseId)->findOrFail($commentId);

        $comment->increment('dislikes');
        
        return response()->json([
            'success' => true,
            'likes' => $comment->likes,
            'dislikes' => $comment->dislikes,
        ]);
    }

}
