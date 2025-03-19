<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles  // Если хотим принимать несколько ролей
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Проверяем, что пользователь залогинен
        if (!Auth::check()) {
            // Если не залогинен, отправляем на страницу входа (или выдаём ошибку)
            return redirect()->route('login');
        }

        // Получаем роль текущего пользователя
        $userRole = Auth::user()->role;

        // Если нужно проверить только одну роль, можно взять $roles[0].
        // Но лучше поддерживать массив ролей:
        if (! in_array($userRole, $roles)) {
            // Если роль не соответствует нужной - отказываем в доступе
            abort(403, 'У вас нет доступа к этой странице');
        }

        return $next($request);
    }
}
