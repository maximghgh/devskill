<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Group;
use App\Models\Topic;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Course $course)
    {
        $groups = $course->groups()
            ->with('topics:id')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Group $group) => $this->withTopicIds($group));

        return response()->json($groups);
    }

    public function show(Course $course, Group $group)
    {
        if ($group->course_id !== $course->id) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $group->load(['students:id,name', 'topics:id']);

        return response()->json($this->withTopicIds($group));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name_group' => 'required|string|max:255',
            'student_ids' => 'array',
            'student_ids.*' => 'integer|exists:users,id',
            'topic_ids' => 'array',
            'topic_ids.*' => 'integer',
        ]);

        $studentIds = $validated['student_ids'] ?? [];

        $group = Group::create([
            'name_group' => $validated['name_group'],
            'course_id' => $course->id,
            'students_count' => count($studentIds),
        ]);

        if (!empty($studentIds)) {
            $group->students()->sync($studentIds);
        }

        // Набор не передан — открываем первую тему курса.
        // Пустой набор означал бы, что зачисленный в группу ученик сразу
        // теряет доступ ко всему курсу; явно переданный пустой массив уважаем.
        $group->topics()->sync(
            array_key_exists('topic_ids', $validated)
                ? $this->courseTopicIds($course, $validated['topic_ids'])
                : $this->firstTopicId($course)
        );

        $group->load('topics:id');

        return response()->json([
            'group' => $this->withTopicIds($group),
        ], 201);
    }

    public function update(Request $request, Course $course, Group $group)
    {
        if ($group->course_id !== $course->id) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $validated = $request->validate([
            'name_group' => 'required|string|max:255',
            'student_ids' => 'array',
            'student_ids.*' => 'integer|exists:users,id',
            'topic_ids' => 'array',
            'topic_ids.*' => 'integer',
        ]);

        $studentIds = $validated['student_ids'] ?? [];

        $group->name_group = $validated['name_group'];
        $group->students_count = count($studentIds);
        $group->save();

        $group->students()->sync($studentIds);

        if (array_key_exists('topic_ids', $validated)) {
            $group->topics()->sync($this->courseTopicIds($course, $validated['topic_ids']));
        }

        $group->load(['students:id,name', 'topics:id']);

        return response()->json([
            'group' => $this->withTopicIds($group),
        ]);
    }

    /**
     * Заменить набор тем, открытых группе.
     * Вызывается со страницы курса у преподавателя.
     */
    public function syncTopics(Request $request, Course $course, Group $group)
    {
        if ($group->course_id !== $course->id) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $validated = $request->validate([
            'topic_ids' => 'present|array',
            'topic_ids.*' => 'integer',
        ]);

        $topicIds = $this->courseTopicIds($course, $validated['topic_ids']);
        $group->topics()->sync($topicIds);

        return response()->json([
            'success' => true,
            'group_id' => $group->id,
            'topic_ids' => $topicIds,
        ]);
    }

    public function destroy(Course $course, Group $group)
    {
        if ($group->course_id !== $course->id) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $group->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Отдаём плоский список id тем вместо вложенных объектов —
     * фронту нужны только идентификаторы для переключателей.
     */
    private function withTopicIds(Group $group): Group
    {
        $group->setAttribute('topic_ids', $group->topics->pluck('id')->all());
        $group->unsetRelation('topics');

        return $group;
    }

    /**
     * Первая тема курса по порядку — стартовый набор новой группы.
     * Пустой массив, если тем на курсе ещё нет.
     */
    private function firstTopicId(Course $course): array
    {
        $id = Topic::where('course_id', $course->id)
            ->orderBy('order')
            ->orderBy('id')
            ->value('id');

        return $id ? [$id] : [];
    }

    /**
     * Отсеиваем темы, не принадлежащие курсу: id приходит с клиента.
     */
    private function courseTopicIds(Course $course, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return Topic::where('course_id', $course->id)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();
    }
}
