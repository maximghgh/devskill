<?php

namespace App\Http\Controllers;

use App\Models\UserChapterProgress;
use App\Models\Chapter;
use App\Models\Topic;
use Illuminate\Http\Request;
use App\Models\FinalTest;

class ChapterController extends Controller
{
    /**
     * Отобразить список глав для конкретной темы.
     */
    public function index($topicId)
    {
        $topic = Topic::findOrFail($topicId);
        $chapters = $topic->chapters()->orderBy('order')->get();
        // return view('admin.chapters.index', compact('topic', 'chapters'));
        return response()->json([
            'topic' => $topic,
            'chapters' => $chapters
        ]);
    }

    /**
     * Показать форму создания новой главы для темы.
     */
    public function create(Topic $topic)
    {
        return view('admin.topic', compact('topic'));
    }

    /**
     * Сохранить новую главу в базе данных.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $topicId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $topicId)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'type'           => 'required|in:video,text,task,terms,presentation',
            'content'        => 'nullable',
            'correct_answer' => 'nullable|string',
            'file'           => 'nullable|file|max:20480',
            'video_url'      => 'nullable|string',
            'points'         => 'required|integer|min:0',
        ]);

        // Обработка файла (если есть)...
        $presentationPath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->storeAs('public/chapters_files', $file->getClientOriginalName());
            $presentationPath = str_replace('public/', 'storage/', $path);
        }

        if ($data['type'] === 'presentation') {
            // — ТОЛЬКО в таблицу final_tests

            $topic    = Topic::findOrFail($topicId);
            $courseId = $topic->course_id;

            FinalTest::create([
                'topic_id'         => $topicId,
                'chapter_id'       => $courseId,  // или опустите это поле, если в миграции nullable
                'questions'        => is_string($data['content'])
                                    ? json_decode($data['content'], true)
                                    : ($data['content'] ?? []),
                'presentation_path'=> $presentationPath,
            ]);

            return response()->json(['success' => true], 201);
        }

        // — для всех остальных типов: ТОЛЬКО в таблицу chapters
        $chapter = Chapter::create([
            'topic_id'         => $topicId,
            'title'            => $data['title'],
            'type'             => $data['type'],
            'content'          => $data['content']        ?? null,
            'correct_answer'   => $data['correct_answer'] ?? null,
            'video_url'        => $data['video_url']      ?? null,
            'presentation_path'=> $presentationPath,
            'points'           => $data['points'],
        ]);

        return response()->json([
            'success' => true,
            'chapter' => $chapter,
        ], 201);
    }

    /**
     * Показать форму редактирования главы.
     */
    public function edit($id)
    {
        $chapter = Chapter::findOrFail($id);
        return view('admin.chapters.edit', compact('chapter'));
    }

    /**
     * Обновить данные главы.
     */
    public function update(Request $request, $topicId, $chapterId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type'  => 'required|string|in:video,text,task,terms,presentation',
        ]);

        $chapter = Chapter::where('topic_id', $topicId)->findOrFail($chapterId);
        $chapter->title          = $request->title;
        $chapter->type           = $request->type;
        $chapter->content        = $request->content; // JSON-контент, если используется Editor.js
        $chapter->video_url      = $request->video_url ?? null;
        $chapter->correct_answer = $request->correct_answer ?? null;
        $chapter->points        = $request->points;
        $chapter->save();

        return response()->json([
            'chapter' => $chapter,
        ]);
    }

    /**
     * Удалить главу.
     */
    public function destroy($topicId, $chapterId)
    {
        $chapter = Chapter::where('topic_id', $topicId)->findOrFail($chapterId);
        $chapter->delete();

        return response()->json([
            'message' => 'Глава успешно удалена',
        ]);
    }

    public function completeChapter(Request $request, Chapter $chapter)
{
    // Берём user_id из запроса (из поля user_id)
    $userId = $request->input('user_id') ?? auth()->id();

    if (!$userId) {
        return response()->json([
            'message' => 'user_id is required'
        ], 400);
    }

    // Создаём или ищем запись
    $progress = UserChapterProgress::firstOrNew([
        'user_id' => $userId,
        'chapter_id' => $chapter->id,
    ]);

    if (!$progress->completed_at) {
        $progress->completed_at = now();
        $progress->save();
    }

    return response()->json([
        'message' => 'Chapter completed',
        'chapter_id' => $chapter->id
    ]);
}
    public function getStats()
    {
        // 1. Общее количество глав
        $totalChapters = \App\Models\Chapter::count();

        // 2. Делаем запрос с агрегацией
        //    - Считаем, сколько глав пройдено каждым пользователем (COUNT)
        //    - Находим последнюю дату прохождения (MAX)
        //    - Имена берём из таблицы users
        //    - Учитываем, что user_chapter_progress может и не быть у некоторых
        $usersProgress = \App\Models\User::select('users.id', 'users.name')
            ->leftJoin('user_chapter_progress', 'users.id', '=', 'user_chapter_progress.user_id')
            ->selectRaw('COUNT(user_chapter_progress.id) AS completed_chapters')
            ->selectRaw('MAX(user_chapter_progress.completed_at) AS last_completed_at')
            ->groupBy('users.id', 'users.name')
            ->orderBy('users.name')
            ->get()
            ->map(function ($user) use ($totalChapters) {
                // Считаем процент, если totalChapters > 0
                $progressPercent = 0;
                if ($totalChapters > 0) {
                    $progressPercent = ($user->completed_chapters / $totalChapters) * 100;
                }

                return [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'completed_chapters'=> $user->completed_chapters,
                    'progress_percent'  => $progressPercent,
                    'last_completed_at' => $user->last_completed_at,
                ];
            });

        // 3. Возвращаем ответ (JSON)
        return response()->json($usersProgress);
    }
    public function showteach(Request $request, $id)
    {
        // // Подгружаем главу, либо 404
        // $chapter = Chapter::find($id);

        // if (! $chapter) {
        //     return response()->json(['message' => 'Chapter not found'], 404);
        // }

        // return response()->json($chapter);   // как и было
        $chapter = Chapter::findOrFail($id);

    // найдём прогресс для текущего user_id
    // (если авторизация настроена — используйте auth()->id())
    $userId = $request->input('user_id') ?? auth()->id();

    $isCompleted = false;
    if ($userId) {
        $isCompleted = UserChapterProgress::where([
            'user_id'    => $userId,
            'chapter_id' => $chapter->id,
        ])->whereNotNull('completed_at')->exists();
    }

    return response()->json([
        'id'                => $chapter->id,
        'topic_id'          => $chapter->topic_id,
        'title'             => $chapter->title,
        'content'           => $chapter->content,
        'type'              => $chapter->type,
        'video_url'         => $chapter->video_url,
        'presentation_path' => $chapter->presentation_path,
        'correct_answer'    => $chapter->correct_answer,
        // вот он:
        'is_completed'      => $isCompleted,
        'created_at'        => $chapter->created_at,
        'updated_at'        => $chapter->updated_at,
    ]);
    }
    public function taskCount($topicId)
    {
        $count = Chapter::where('topic_id', $topicId)
                        ->where('type', 'task')
                        ->count();

        return response()->json([
            'taskCount' => $count
        ]);
    }
    // в ChapterController.php
    public function finalTest(Chapter $chapter)
    {
        // грузим связь finalTest (модель FinalTest с полем questions)
        $ft = $chapter->finalTest;
        if (! $ft) {
            return response()->json(['message' => 'Test not found'], 404);
        }
        return response()->json([
            'questions' => $ft->questions,   // массив {id, prompt, type, options, answer}
            'settings'  => $ft->settings ?? [],
        ]);
    }
    public function complete(Chapter $chapter, Request $r)
    {
        $userId = $r->user_id ?? auth()->id();
        UserProgress::updateOrCreate(
            ['user_id'=>$userId,'chapter_id'=>$chapter->id],
            ['completed_at'=>now()]
        );
        return response()->noContent();
    }


}
