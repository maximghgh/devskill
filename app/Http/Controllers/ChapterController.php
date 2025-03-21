<?php

namespace App\Http\Controllers;

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
    public function update(Request $request, $id)
    {
        $chapter = Chapter::findOrFail($id);

        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'type'       => 'required|in:video,text,task,terms,quiz',
            'content'    => 'nullable',
            'video_url'  => 'nullable|string',
            'order'      => 'nullable|integer',
        ]);

        $chapter->update($validated);

        return response()->json([
            'success' => true,
            'chapter' => $chapter,
            'message' => 'Глава успешно обновлена'
        ], 200);
    }

    /**
     * Удалить главу.
     */
    public function destroy($id)
    {
        $chapter = Chapter::findOrFail($id);
        $chapter->delete();

        return response()->json([
            'success' => true,
            'message' => 'Глава успешно удалена'
        ]);
    }
}
