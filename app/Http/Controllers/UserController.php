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
        $users = User::select('id', 'name', 'email', 'phone', 'country', 'role', 'birthday', 'created_at', 'photo', 'position','inn')
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
        // 1. Получаем все course_id, которые купил пользователь
        $courseIds = Purchase::where('user_id', $id)->pluck('course_id');

        // 2. Загружаем курсы, вместе с темами и главами (eager loading)
        $courses = \App\Models\Course::whereIn('id', $courseIds)
            ->with('topics.chapters')
            ->get();

        // 3. Загружаем прогресс пользователя
        //    Предполагается, что есть модель UserChapterProgress,
        //    которая хранит записи вида (user_id, chapter_id, completed_at, ...)
        $progressRows = \App\Models\UserChapterProgress::where('user_id', $id)->get();
        $completedChapterIds = $progressRows->pluck('chapter_id')->unique();

        // 4. Проставляем is_completed = true/false каждой главе
        foreach ($courses as $course) {
            foreach ($course->topics as $topic) {
                foreach ($topic->chapters as $chapter) {
                    // Проверяем, есть ли chapter_id в списке пройденных
                    $chapter->is_completed = $completedChapterIds->contains($chapter->id);
                }
            }
        }

        // 5. Возвращаем JSON-ответ
        return response()->json([
            'courses' => $courses
        ]);
    }

    public function update(Request $request, $id)
    {
        // Валидируем входные данные
        $validated = $request->validate([
            'name'     => 'nullable|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'nullable|string|max:50',
            'birthday' => 'nullable|date',
            'country'  => 'nullable|string|max:100',
            'role'     => 'required|in:1,2,3',
            'position' => 'nullable|string|max:255',
        ]);

        // Ищем пользователя по ID
        $user = User::findOrFail($id);

        // Обновляем поля пользователя
        // Обновляем все поля разом
        $user->update($validated);

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
