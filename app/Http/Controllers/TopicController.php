<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Course;
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
            'topics' => $topics
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
    public function store(Request $request)
    {
        // Берём из маршрута параметр "course" (зависит от того, как назвали в Route)
        $courseId = $request->route('course'); 

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'order'       => 'nullable|integer',
        ]);

        $validated['course_id'] = $courseId;

        $topic = Topic::create($validated);

        return response()->json([
            'success' => true,
            'topic'   => $topic,
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
    public function update(Request $request, $id)
    {
        $topic = Topic::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'order'       => 'nullable|integer',
        ]);

        $topic->update($request->all());

        return redirect()->route('admin.topics.index', ['course' => $topic->course_id])
                         ->with('success', 'Тема успешно обновлена');
    }

    /**
     * Удалить тему.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $topic = Topic::findOrFail($id);
        $courseId = $topic->course_id;
        $topic->delete();

        return redirect()->route('admin.topics.index', ['course' => $courseId])
                         ->with('success', 'Тема успешно удалена');
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
            'topic' => $topic
        ]);
    }

}
