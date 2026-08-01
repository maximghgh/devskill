<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        // Получаем id пользователя из запроса (его вы можете передавать, например, вместе с form)
        $userId = $request->input('id');
        if (! $userId) {
            return response()->json(['message' => 'Не указан id пользователя'], 400);
        }

        $user = User::findOrFail($userId);

        // Валидируем данные
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // исключаем текущего пользователя из проверки уникальности email
            'email' => 'nullable|email|unique:users,email,'.$user->id,
            'birthday' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            // Блок родителя приходит только с вкладки «Родитель».
            // Если он передан — ФИО, e-mail и телефон обязательны;
            // профили админа и преподавателя его не шлют и не ломаются.
            'parent_info' => 'nullable|array',
            'parent_info.name' => 'required_with:parent_info|string|max:255',
            'parent_info.email' => 'required_with:parent_info|email|max:255',
            'parent_info.phone' => 'required_with:parent_info|string|max:20',
            'parent_info.birthday' => 'nullable|date',
            'parent_info.country' => 'nullable|string|max:255',
            'student_info' => 'nullable|array',
            'student_info.name' => 'nullable|string|max:255',
            'student_info.email' => 'nullable|email|max:255',
            'student_info.birthday' => 'nullable|date',
            'student_info.phone' => 'nullable|string|max:20',
            'student_info.country' => 'nullable|string|max:255',
        ], [
            'parent_info.name.required_with' => 'Укажите ФИО родителя.',
            'parent_info.email.required_with' => 'Укажите e-mail родителя.',
            'parent_info.phone.required_with' => 'Укажите телефон родителя.',
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'birthday' => $validated['birthday'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
        ];

        // Блоки перезаписываем только если они реально пришли в запросе,
        // иначе сохранённые данные затёрлись бы пустыми значениями.
        if ($request->has('parent_info')) {
            $payload['parent_info'] = $this->profileInfo($validated['parent_info'] ?? []);
        }
        if ($request->has('student_info')) {
            $payload['student_info'] = $this->profileInfo($validated['student_info'] ?? []);
        }

        $user->update($payload);

        return response()->json([
            'message' => 'Данные успешно обновлены!',
            'user' => new UserResource($user),
        ], 200);
    }

    private function profileInfo(array $info): array
    {
        return [
            'name' => $info['name'] ?? null,
            'email' => $info['email'] ?? null,
            'birthday' => $info['birthday'] ?? null,
            'phone' => $info['phone'] ?? null,
            'country' => $info['country'] ?? null,
        ];
    }
}
