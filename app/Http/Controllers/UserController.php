<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Purchase;


class UserController extends Controller
{
    public function index()
    {
        // Получаем всех пользователей
        $users = User::select('id', 'name', 'email', 'phone', 'country', 'role', 'birthday', 'created_at')
                     ->orderBy('id', 'asc')
                     ->get();

        // Возвращаем JSON
        return response()->json($users);
    }
    public function show($id)
    {
        // Ищем пользователя по ID или возвращаем 404, если не найден
        $user = User::findOrFail($id);
        return response()->json($user);
    }
    public function getByIds(Request $request)
    {
        // Массив ID, которые передаёт фронтенд
        $ids = $request->input('ids', []);
        // Получаем пользователей с этими ID
        $users = User::whereIn('id', $ids)->get();
        return response()->json($users);
    }

    public function getPurchasedCourses($id)
{
    // Сначала получаем все course_id, которые купил пользователь
    $courseIds = Purchase::where('user_id', $id)->pluck('course_id');

    // Потом вытаскиваем все курсы по этим ID
    $courses = \App\Models\Course::whereIn('id', $courseIds)->get();

    return response()->json([
        'courses' => $courses
    ]);
}

}
