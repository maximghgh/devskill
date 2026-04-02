<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Models\PendingUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use App\Mail\VerificationCodeMail;

class AuthController extends Controller
{
    // Регистрация пользователя
    public function register(Request $request)
    {
        \Log::info('📥 Полученные данные в register:', $request->all());

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:5',
                'inn'      => 'required|digits_between:10,12',
            ], [
                'email.unique' => 'Пользователь с такой почтой уже существует.',
                'password.min' => 'Пароль должен содержать не менее 5 символов.',
                'inn.digits_between' => 'ИНН должен содержать 10 или 12 цифр.',
            ]);

            $token = sha1($request->email . now());
            $expiresAt = Carbon::now()->addMinutes(60);

            // Создаём или обновляем временную запись (если email уже есть в pending_users)
            $pendingUser = PendingUser::updateOrCreate(
                ['email' => $request->email],
                [
                    'name'       => $request->name,
                    'password'   => Hash::make($request->password),
                    'inn'        => $request->inn,
                    'token'      => $token,
                    'expires_at' => $expiresAt,
                ]
            );

            // Генерируем временную подписанную ссылку подтверждения
            $verificationUrl = URL::temporarySignedRoute(
                'verify.email',
                Carbon::now()->addMinutes(60),
                ['token' => $pendingUser->token]
            );

            // Отправляем письмо с подтверждением
            Mail::to($request->email)->send(new VerifyEmail($verificationUrl, $request->name));

            return response()->json([
                'success'     => true,
                'message'     => 'Проверьте почту для подтверждения аккаунта',
                'email_sent'  => true,
                'redirect'    => '/verify'
            ], 200);
        } catch (ValidationException $e) {
            \Log::error('⛔ Ошибка валидации:', $e->errors());
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('❌ Ошибка регистрации:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Ошибка регистрации: ' . $e->getMessage()], 500);
        }
    }

    // Подтверждение аккаунта по ссылке
    public function verifyEmail(Request $request, $token)
    {
        $pendingUser = PendingUser::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$pendingUser) {
            return redirect('/')->with('error', '❌ Недействительная или устаревшая ссылка.');
        }

        if (User::where('email', $pendingUser->email)->exists()) {
            return redirect('/')->with('error', '❌ Этот email уже подтверждён.');
        }

        // Создаём реального пользователя
        $user = User::create([
            'name'              => $pendingUser->name,
            'email'             => $pendingUser->email,
            'password'          => $pendingUser->password, // уже зашифрован
            'inn'               => $pendingUser->inn,
            'email_verified_at' => now()
        ]);

        // Удаляем временные данные
        $pendingUser->delete();

        // Кодируем необходимые поля в base64
        $encodedUser = base64_encode(json_encode([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email
        ]));

        // Перенаправляем на главную с параметром ?verifiedUser=...
        return redirect("/?verifiedUser={$encodedUser}");
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::validate(['email' => $request->login, 'password' => $request->password])) {
            $user = \App\Models\User::where('email', $request->login)->first();

            $verificationCode = rand(100000, 999999);
            session(['verification_code' => $verificationCode, 'user_id' => $user->id]);

            Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 401);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6'
        ]);

        $expectedCode = (string) session('verification_code', '');
        $userId = session('user_id');

        if ($expectedCode === '' || empty($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Код подтверждения истёк. Войдите заново.',
            ], 422);
        }

        if ((string) $request->code === $expectedCode) {
            Auth::loginUsingId((int) $userId);
            $request->session()->regenerate();
            session()->forget(['verification_code', 'user_id']);

            return response()->json(['success' => true, 'user' => Auth::user()]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Неверный код. Попробуйте снова.',
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }

    public function resendEmail(Request $request)
    {
        // Получаем email из запроса
        $email = $request->input('user_email');

        // Находим PendingUser по email
        $pendingUser = PendingUser::where('email', $email)->first();
        if (!$pendingUser) {
            return back()->with('error', 'Пользователь не найден или уже подтверждён.');
        }

        // (Опционально) можно обновить expires_at для продления срока действия
        // $pendingUser->expires_at = Carbon::now()->addMinutes(60);
        // $pendingUser->save();

        // Генерируем ссылку, используя сохранённый token
        $verificationUrl = URL::temporarySignedRoute(
            'verify.email',
            Carbon::now()->addMinutes(60),
            ['token' => $pendingUser->token]
        );

        // Отправляем письмо с подтверждением
        Mail::to($pendingUser->email)->send(new VerifyEmail($verificationUrl, $pendingUser->name));

        return back()->with('status', 'Ссылка отправлена повторно!');
    }



}
