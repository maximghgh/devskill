<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Course;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Обработка заявки на консультацию.
     *
     * @param Request $request
     * @param Course $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Course $course)
    {
        // Если требуется авторизация, можно получить текущего пользователя:
        $user = $request->user();

        $validated = $request->validate([
            'user_id' => 'required|integer',
            'email' => 'required|email',
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        $consultation = Consultation::create([
            'user_id' => $validated['user_id'],
            'course_id' => $course->id,
            'email' => $validated['email'],
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'status' => 'new'
        ]);

        return response()->json([
            'success' => true,
            'consultation' => $consultation,
            'message' => 'Заявка на консультацию успешно отправлена'
        ], 201);
    }
    public function index()
    {
        $consultations = \App\Models\Consultation::query()
            ->leftJoin('courses', 'consultations.course_id', '=', 'courses.id')
            ->select(
                'consultations.*',
                'courses.card_title as course_title'
            )
            // Показываем только те, что НЕ "выполнено"
            ->where('consultations.status', '!=', 'выполнено')
            ->orderBy('consultations.created_at', 'desc')
            ->get();

        return response()->json($consultations);
    }

    public function complete($id)
    {
        // Находим запись
        $consultation = Consultation::findOrFail($id);

        // Меняем статус на "выполнено" (можно использовать другое слово)
        $consultation->status = 'выполнено';
        $consultation->save();

        return response()->json([
            'success' => true,
            'message' => 'Статус консультации изменён на выполнено',
        ]);
    }
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string'
        ]);
    
        $consultation = \App\Models\Consultation::findOrFail($id);
        $consultation->status = $validated['status'];
        $consultation->save();
    
        return response()->json([
            'success' => true,
            'message' => 'Статус консультации обновлён',
        ]);
    }

}
