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
        'title'   => 'required|string|max:255',
        'type'    => 'required|in:video,text,task,terms,presentation',
        'content' => 'nullable',
        'file'    => 'nullable|file|max:20480',  // единое правило
    ]);

    // Привяжем к теме
    $validated['topic_id'] = $topicId;

    // Сериализуем editor.js-поле, если надо
    if (isset($validated['content']) && is_array($validated['content'])) {
        $validated['content'] = json_encode($validated['content']);
    }

    // Если пришёл файл — сохраняем его в public и пишем путь в presentation_path
    if ($request->hasFile('file')) {

        $file       = $request->file('file');
    
        // ► как называется файл у пользователя
        $original   = $file->getClientOriginalName();        // docs.pdf
    
              // или любой алгоритм
    
        // кладём в storage/app/public/chapters_files/…
        $path = $file->storeAs('public/chapters_files', $original);
    
        // путь, по которому фронт сможет достучаться:  /storage/chapters_files/…
        $validated['presentation_path'] = str_replace('public/', 'storage/', $path);
    }

    $chapter = Chapter::create($validated);

    return response()->json([
        'success' => true,
        'chapter' => $chapter->fresh(),
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


}
