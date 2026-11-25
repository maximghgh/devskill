<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseApplication;
use App\Services\CourseAccessService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseApplicationController extends Controller
{
    public function __construct(private CourseAccessService $access)
    {
    }

    /**
     * Заявка с карточки курса. Доступна и гостю: пользователя к заявке
     * админ привязывает вручную, когда заведёт ему аккаунт.
     */
    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        // Группа api не stateful, поэтому сессию здесь не видно:
        // авторизованный фронт присылает user_id сам, как и остальные эндпоинты.
        $application = CourseApplication::create([
            'course_id' => $course->id,
            'user_id' => $request->user()?->id ?? ($validated['user_id'] ?? null),
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'status' => CourseApplication::STATUS_AWAITING_SIGNING,
        ]);

        return response()->json([
            'success' => true,
            'application' => $application,
        ], 201);
    }

    /** Список заявок для админки. */
    public function index()
    {
        $applications = CourseApplication::query()
            ->with([
                'course:id,card_title,course_name,price',
                'user:id,name,login,email',
            ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($applications);
    }

    /** Смена статуса, привязка пользователя, заметка администратора. */
    public function update(Request $request, CourseApplication $application)
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(CourseApplication::STATUSES)],
            'user_id' => 'sometimes|nullable|integer|exists:users,id',
            'admin_comment' => 'sometimes|nullable|string',
        ]);

        if (array_key_exists('user_id', $validated)) {
            $application->user_id = $validated['user_id'];
        }

        if (array_key_exists('admin_comment', $validated)) {
            $application->admin_comment = $validated['admin_comment'];
        }

        $previousStatus = $application->status;
        $nextStatus = $validated['status'] ?? $previousStatus;

        // Открывать курс некому, пока заявка не привязана к пользователю.
        if ($nextStatus === CourseApplication::STATUS_ISSUED && !$application->user_id) {
            return response()->json([
                'message' => 'Сначала привяжите пользователя к заявке — иначе курс некому открыть.',
            ], 422);
        }

        $application->status = $nextStatus;
        $application->save();

        if ($nextStatus === CourseApplication::STATUS_ISSUED) {
            $this->access->openCourseAccess($application);

            if ($previousStatus !== CourseApplication::STATUS_ISSUED) {
                $this->access->notifyCourseIssued($application);
            }
        }

        if ($nextStatus === CourseApplication::STATUS_CLOSED) {
            $this->access->closeCourseAccess($application);
        }

        $application->load([
            'course:id,card_title,course_name,price',
            'user:id,name,login,email',
        ]);

        return response()->json([
            'success' => true,
            'application' => $application,
        ]);
    }

    /** Заявки пользователя — для раздела «Мои курсы» в личном кабинете. */
    public function forUser($id)
    {
        $applications = CourseApplication::query()
            ->where('user_id', $id)
            ->with('course:id,card_title,course_name,price,card_image,direction,difficulty')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($applications);
    }

    public function destroy(CourseApplication $application)
    {
        $application->delete();

        return response()->json(['success' => true]);
    }
}
