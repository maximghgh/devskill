<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Course;
use App\Http\Requests\StoreTopicRequest;
use App\Http\Requests\UpdateTopicRequest;
use App\Http\Resources\TopicResource;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Отобразить список тем для конкретного курса.
     *
     * @param int $courseId
     * @return \Illuminate\View\View
     */
    public function index($courseId)
    {
        $course = Course::findOrFail($courseId);
        // Добавляем подсчет глав
        $topics = $course->topics()->withCount('chapters')->orderBy('order')->get();
        
        return response()->json([
            'topics' => TopicResource::collection($topics),
        ]);
        
    }
    public function getTopicsJson($courseId)
    {
        return view('admin.course');
    }

    /**
     * Показать форму создания новой темы для курса.
     *
     * @param int $courseId
     * @return \Illuminate\View\View
     */
    public function create($courseId)
    {
        $course = Course::findOrFail($courseId);
        return view('admin.topics.create', compact('course'));
    }

    /**
     * Сохранить новую тему в базе данных.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $courseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreTopicRequest $request)
    {
        // Берём из маршрута параметр "course" (зависит от того, как назвали в Route)
        $courseId = $request->route('course'); 

        $validated = $request->validated();

        $validated['course_id'] = $courseId;

        $topic = Topic::create($validated);

        return response()->json([
            'success' => true,
            'topic'   => new TopicResource($topic),
            'message' => 'Тема успешно создана'
        ], 201);
    }



    /**
     * Показать форму редактирования темы.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $topic = Topic::findOrFail($id);
        return view('admin.topics.edit', compact('topic'));
    }

    /**
     * Обновить данные темы.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateTopicRequest $request, $topicId)
    {
        $topic = Topic::findOrFail($topicId);

        $validated = $request->validated();

        $topic->update($validated);

        return response()->json([
            'success' => true,
            'topic'   => new TopicResource($topic),
        ]);
    }

    public function destroy($topicId)
    {
        $topic = Topic::findOrFail($topicId);
        $topic->delete();

        return response()->json([
            'success' => true,
            'message' => 'Тема удалена'
        ]);
    }
    // AdminCourseController.php
    public function show(Course $course)
    {
        // $course->id = 20
        return view('admin.course.show', compact('course'));
    }
    public function showes(Topic $topic)
    {
        return response()->json([
            'topic' => new TopicResource($topic),
        ]);
    }

}
