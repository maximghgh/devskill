<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        // Получаем id пользователя из запроса (его вы можете передавать, например, вместе с form)
        $userId = $request->input('id');
        if (!$userId) {
            return response()->json(['message' => 'Не указан id пользователя'], 400);
        }

        $user = User::findOrFail($userId);

        // Валидируем данные
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            // исключаем текущего пользователя из проверки уникальности email
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'birthday' => 'nullable|date',
            'phone'    => 'nullable|string|max:20',
            'country'  => 'nullable|string|max:255',
        ]);

        // Обновляем запись в таблице
        $user->update($validated);

        return response()->json([
            'message' => 'Данные успешно обновлены!',
            'user'    => $user
        ], 200);
    }
}
