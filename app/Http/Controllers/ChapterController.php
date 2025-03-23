<?php

namespace App\Http\Controllers;

use App\Models\UserChapterProgress;
use App\Models\Chapter;
use App\Models\Topic;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type'  => 'required|in:video,text,task,terms,quiz',
            'content' => 'nullable', // Массив или объект
            'video_url' => 'nullable|string',
            'correct_answer' => 'nullable|string', // <-- добавили
        ]);

        $validated['topic_id'] = $topicId;

        // Если 'content' пришел массивом/объектом, нужно превратить в строку JSON
        if (is_array($validated['content']) || is_object($validated['content'])) {
            $validated['content'] = json_encode($validated['content']);
        }

        $chapter = Chapter::create($validated);

        return response()->json([
            'success' => true,
            'chapter' => $chapter
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
            'type'  => 'required|string|in:video,text,task,terms',
        ]);

        $chapter = Chapter::where('topic_id', $topicId)->findOrFail($chapterId);
        $chapter->title          = $request->title;
        $chapter->type           = $request->type;
        $chapter->content        = $request->content; // JSON-контент, если используется Editor.js
        $chapter->video_url      = $request->video_url ?? null;
        $chapter->correct_answer = $request->correct_answer ?? null;
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


}
