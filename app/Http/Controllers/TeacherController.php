<?php 
namespace App\Http\Controllers;

use App\Models\FinalTestResult;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        // можно передать в view любые данные, например авторизованного юзера
        return view('app');
    }

    /**
     * Получить расписание преподавателя (свободный текст).
     */
    public function getSchedule($teacherId)
    {
        $teacher = User::findOrFail($teacherId);

        return response()->json([
            'schedule' => $teacher->schedule ?? '',
        ]);
    }

    /**
     * Сохранить расписание преподавателя (свободный текст).
     */
    public function updateSchedule(Request $request, $teacherId)
    {
        $teacher = User::findOrFail($teacherId);

        $validated = $request->validate([
            'schedule' => 'nullable|string|max:5000',
        ]);

        $teacher->schedule = $validated['schedule'] ?? '';
        $teacher->save();

        return response()->json([
            'success' => true,
            'schedule' => $teacher->schedule,
        ]);
    }
    public function studentsResults($teacherId)
    {
        $courseIds = Course::whereJsonContains('teachers', $teacherId)
                   ->pluck('id')
                   ->toArray();
        if (empty($courseIds)) {
            return response()->json([], 200);
        }

        $rows = FinalTestResult::with('user')
            ->whereIn('course_id', $courseIds)
            ->get()
            ->map(function($r) {
                return [
                    'student_name' => $r->user->name,
                    'answer_q1'    => (array) ($r->answers[1] ?? []),
                ];
            });

        return response()->json($rows, 200);
    }
}

?>