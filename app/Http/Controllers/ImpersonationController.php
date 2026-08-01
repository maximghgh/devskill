<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Вход администратора под аккаунтом пользователя.
 *
 * Живёт на web-роутах, а не в api.php: только у группы web есть сессия
 * (в api-группе Sanctum stateful отключён), а вход выполняется именно
 * через сессионный guard.
 */
class ImpersonationController extends Controller
{
    /** Ключ сессии, в котором храним id настоящего администратора. */
    private const SESSION_KEY = 'impersonator_id';

    /**
     * Начать работу под аккаунтом пользователя.
     */
    public function start(Request $request, User $user): JsonResponse
    {
        $admin = Auth::user();

        if ($admin->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя войти под собственным аккаунтом.',
            ], 422);
        }

        // Под другим администратором заходить запрещаем: это даёт обход
        // ограничений и запутывает журнал действий.
        if ((int) $user->role === 3) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя войти под аккаунтом другого администратора.',
            ], 403);
        }

        // Уже находимся под чужим аккаунтом — не даём выстроить цепочку.
        if ($request->session()->has(self::SESSION_KEY)) {
            return response()->json([
                'success' => false,
                'message' => 'Вы уже работаете под другим аккаунтом. Сначала вернитесь в админку.',
            ], 422);
        }

        $adminId = $admin->id;

        Auth::loginUsingId($user->id);
        $request->session()->regenerate();
        // Ставим метку ПОСЛЕ regenerate(), чтобы она гарантированно осталась в сессии.
        $request->session()->put(self::SESSION_KEY, $adminId);

        // Доступ администратора к личным данным пользователя фиксируем в журнале.
        Log::info('Вход администратора под аккаунтом пользователя', [
            'admin_id' => $adminId,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'user' => new UserResource(Auth::user()),
            'impersonator' => new UserResource(User::find($adminId)),
        ]);
    }

    /**
     * Вернуться в собственный аккаунт администратора.
     */
    public function stop(Request $request): JsonResponse
    {
        // Метку читаем ДО любых операций, очищающих сессию.
        $adminId = $request->session()->get(self::SESSION_KEY);

        if (!$adminId) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не работаете под чужим аккаунтом.',
            ], 422);
        }

        $admin = User::find($adminId);

        if (!$admin || (int) $admin->role !== 3) {
            $request->session()->forget(self::SESSION_KEY);

            return response()->json([
                'success' => false,
                'message' => 'Исходный аккаунт администратора недоступен.',
            ], 422);
        }

        $impersonatedId = Auth::id();

        Auth::loginUsingId($admin->id);
        $request->session()->regenerate();
        $request->session()->forget(self::SESSION_KEY);

        Log::info('Возврат администратора в свой аккаунт', [
            'admin_id' => $admin->id,
            'user_id' => $impersonatedId,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'user' => new UserResource(Auth::user()),
        ]);
    }

    /**
     * Текущее состояние: работаем ли мы сейчас под чужим аккаунтом.
     * Нужен фронту, чтобы восстановить баннер после перезагрузки страницы.
     */
    public function status(Request $request): JsonResponse
    {
        $adminId = $request->session()->get(self::SESSION_KEY);

        if (!$adminId || !Auth::check()) {
            return response()->json(['impersonating' => false]);
        }

        return response()->json([
            'impersonating' => true,
            'user' => new UserResource(Auth::user()),
            'impersonator' => new UserResource(User::find($adminId)),
        ]);
    }
}
