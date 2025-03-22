<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetCodeMail; // Mailable для отправки кода

class ForgotPasswordController extends Controller
{
    /**
     * Отправка кода восстановления пароля на e-mail.
     */
    public function sendResetCode(Request $request)
    {
        // Валидация входных данных (при необходимости можно вынести в Form Request)
        $request->validate([
            'login' => 'required|email',
        ]);

        // Ищем пользователя по e-mail
        $user = User::where('email', $request->login)->first();
        if (!$user) {
            // FIX: Возвращаем JSON-ответ с ошибкой, если пользователь не найден
            return response()->json(['error' => 'Пользователь с таким e-mail не найден'], 422);
        }

        // Генерируем случайный 6-значный код
        // FIX: Используем random_int для повышения криптографической безопасности
        $code = random_int(100000, 999999);

        // Сохраняем или обновляем запись в таблице password_resets
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => $code,
                'created_at' => Carbon::now(),
            ]
        );

        // Отправляем письмо с кодом на e-mail пользователя
        try {
            Mail::to($user->email)->send(new ResetCodeMail($code));
        } catch (\Exception $e) {
            // FIX: Обработка ошибок при отправке почты
            return response()->json(['error' => 'Ошибка при отправке письма: ' . $e->getMessage()], 500);
        }

        // Возвращаем успешный JSON-ответ
        return response()->json(['status' => 'Код отправлен на вашу почту']);
    }

    /**
     * Сброс пароля пользователя.
     */
    public function resetPassword(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        // Ищем запись в таблице password_resets по e-mail
        $record = DB::table('password_resets')->where('email', $request->email)->first();
        if (!$record || $record->token != $request->code) {
            // FIX: Возвращаем JSON-ответ с ошибкой, если код неверный
            return response()->json(['error' => 'Неверный код подтверждения'], 422);
        }

        // Проверяем, не истёк ли срок действия кода (например, 60 минут)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return response()->json(['error' => 'Код устарел, запросите новый'], 422);
        }

        // Находим пользователя и обновляем его пароль
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден'], 422);
        }
        $user->password = Hash::make($request->password);
        $user->save();

        // Удаляем запись о сбросе пароля, так как она уже не нужна
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['status' => 'Пароль успешно изменён']);
    }
}
