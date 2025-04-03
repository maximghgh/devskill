<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * Обработать покупку курса.
     *
     * @param Request $request
     * @param Course $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Course $course)
    {
        // Валидируем входные данные
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'payment_method' => 'required|in:card,sbp',
            'payment_details' => 'nullable|json',
        ]);

        // Если payment_details пришёл как массив или объект
        if (is_array($validated['payment_details']) || is_object($validated['payment_details'])) {
            $validated['payment_details'] = json_encode($validated['payment_details']);
        }

        // Создаём покупку
        $purchase = Purchase::create([
            'user_id' => $validated['user_id'], // <-- Берём из запроса
            'course_id' => $course->id,         // <-- Берём из параметра URL (Route Model Binding)
            'payment_method' => $validated['payment_method'],
            'payment_details' => $validated['payment_details'],
            'status' => 'completed', // Или 'pending'
        ]);

        return response()->json([
            'success' => true,
            'purchase' => $purchase,
            'message' => 'Курс успешно куплен'
        ], 201);
    }
    public function index()
    {
        // Берём все покупки, присоединяем пользователей и курсы
        $purchases = Purchase::query()
            ->join('users', 'purchases.user_id', '=', 'users.id')
            ->join('courses', 'purchases.course_id', '=', 'courses.id')
            ->select(
                'purchases.id',
                'purchases.payment_method',
                'purchases.status',
                'purchases.created_at as purchase_date', // дата покупки
                'users.name as user_name',               // имя пользователя
                'courses.card_title as course_title'     // название курса (или другое поле)
            )
            ->orderBy('purchases.created_at', 'desc')
            ->get();

        return response()->json($purchases);
    }
}

