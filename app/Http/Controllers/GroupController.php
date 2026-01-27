<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Course $course)
    {
        $groups = $course->groups()->orderByDesc('created_at')->get();

        return response()->json($groups);
    }

    public function show(Course $course, Group $group)
    {
        if ($group->course_id !== $course->id) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $group->load(['students:id,name']);

        return response()->json($group);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name_group' => 'required|string|max:255',
            'student_ids' => 'array',
            'student_ids.*' => 'integer|exists:users,id',
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

        return response()->json([
            'group' => $group,
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
        ]);

        $studentIds = $validated['student_ids'] ?? [];

        $group->name_group = $validated['name_group'];
        $group->students_count = count($studentIds);
        $group->save();

        $group->students()->sync($studentIds);
        $group->load(['students:id,name']);

        return response()->json([
            'group' => $group,
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
}
