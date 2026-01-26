<?php

namespace App\Http\Controllers;

use App\Models\UserChapterProgress;
use App\Models\Chapter;
use App\Models\Topic;
use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Http\Resources\ChapterResource;
use Illuminate\Http\Request;
use App\Models\FinalTest;
use Illuminate\Support\Facades\Storage;

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
            'chapters' => ChapterResource::collection($chapters),
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
    public function store(StoreChapterRequest $request, $topicId)
    {
        $data = $request->validated();

        // Обработка файла (если есть)...
        $presentationPaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $name = time().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('public/chapters_files', $name);
                $presentationPaths[] = str_replace('public/', 'storage/', $path);
            }
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('public/chapters_files', $name);
            $presentationPaths[] = str_replace('public/', 'storage/', $path);
        }
        $presentationPath = $presentationPaths[0] ?? null;

        // — для всех остальных типов: ТОЛЬКО в таблицу chapters
        $chapter = Chapter::create([
            'topic_id'         => $topicId,
            'title'            => $data['title'],
            'type'             => $data['type'],
            'content'          => $data['content']        ?? null,
            'correct_answer'   => $data['correct_answer'] ?? null,
            'video_url'        => $data['video_url']      ?? null,
            'presentation_path'=> $presentationPath,
            'presentation_paths'=> $presentationPaths ?: null,
            'points'           => $data['points'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'chapter' => new ChapterResource($chapter),
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
    public function update(UpdateChapterRequest $request, $topicId, $chapterId)
    {
        $data = $request->validated();

        $chapter = Chapter::where('topic_id', $topicId)->findOrFail($chapterId);

        $chapter->title          = $data['title'];
        $chapter->type           = $data['type'];
        $chapter->content        = $data['content'] ?? null;
        $chapter->video_url      = $data['video_url'] ?? null;
        $chapter->correct_answer = $data['correct_answer'] ?? null;
        $chapter->points         = $data['points'] ?? ($chapter->points ?? 0);

        $existing = $chapter->presentation_paths ?? [];
        if (!is_array($existing) || !count($existing)) {
            $existing = $chapter->presentation_path ? [$chapter->presentation_path] : [];
        }

        $retain = $request->input('retain_files', null);
        if (is_string($retain)) {
            $decoded = json_decode($retain, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $retain = $decoded;
            }
        }
        if (is_array($retain)) {
            $existingSet = array_flip($existing);
            $retain = array_values(array_filter($retain, function ($path) use ($existingSet) {
                return isset($existingSet[$path]);
            }));
        } else {
            $retain = null;
        }

        $incoming = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $name = time().'_'.$file->getClientOriginalName();
                $path = $file->storeAs('public/chapters_files', $name);
                $incoming[] = str_replace('public/', 'storage/', $path);
            }
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('public/chapters_files', $name);
            $incoming[] = str_replace('public/', 'storage/', $path);
        }

        $retained = $retain !== null ? $retain : $existing;
        $finalFiles = array_values(array_unique(array_merge($retained, $incoming)));

        $removed = array_diff($existing, $retained);
        foreach ($removed as $oldPath) {
            $old = str_replace('storage/', 'public/', $oldPath);
            Storage::delete($old);
        }

        if ($retain !== null || count($incoming)) {
            $chapter->presentation_paths = count($finalFiles) ? $finalFiles : null;
            $chapter->presentation_path = $finalFiles[0] ?? null;
        }

        $chapter->save();

        return response()->json(['chapter' => new ChapterResource($chapter)]);
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

        $chapter->setAttribute('is_completed', $isCompleted);

        return response()->json(new ChapterResource($chapter));
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
