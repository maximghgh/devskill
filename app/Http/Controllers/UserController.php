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
        $users = User::select('id', 'name', 'email', 'phone', 'country', 'role', 'birthday', 'created_at', 'photo')
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
    public function update(Request $request, $id)
    {
        // Валидируем входные данные
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'phone'    => 'nullable|string|max:50',
            'birthday' => 'nullable|date',
            'country'  => 'nullable|string|max:100',
            'role'     => 'required|in:1,2,3',
        ]);

        // Ищем пользователя по ID
        $user = User::findOrFail($id);

        // Обновляем поля пользователя
        $user->name     = $validated['name'];
        $user->email    = $validated['email'];
        $user->phone    = $validated['phone'] ?? $user->phone;
        $user->birthday = $validated['birthday'] ?? $user->birthday;
        $user->country  = $validated['country'] ?? $user->country;
        $user->role     = $validated['role'];

        // Сохраняем изменения в базе
        $user->save();

        return response()->json([
            'success' => true,
            'user'    => $user,
        ]);
    }
    public function destroy($id)
    {
        // Находим пользователя по ID или возвращаем 404
        $user = User::findOrFail($id);
        
        // Удаляем пользователя
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Пользователь успешно удалён'
        ]);
    }
    public function updateAvatar(Request $request, $id)
    {
        // Находим пользователя по ID
        $user = User::findOrFail($id);

        // Валидируем файл
        $request->validate([
            'file' => 'nullable|image|mimes:jpg,png,jpeg,webp|max:2048',
        ]);

        // Если файл передан
        if ($request->hasFile('file')) {
            // Сохраняем файл в storage/app/public/avatars
            $path = $request->file('file')->store('avatars', 'public');
            // В поле photo пишем путь, например "avatars/xxxxxx.jpg"
            $user->photo = $path;
        }

        // Сохраняем изменения
        $user->save();

        return response()->json([
            'success' => true,
            'user'    => $user,
        ]);
    }

}
