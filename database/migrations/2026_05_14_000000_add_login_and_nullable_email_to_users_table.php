<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'login')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('login')->nullable()->unique()->after('name');
            });
        }

        $this->fillMissingLogins();
        $this->makeEmailNullable();
    }

    public function down(): void
    {
        //
    }

    private function fillMissingLogins(): void
    {
        $used = DB::table('users')
            ->whereNotNull('login')
            ->pluck('login')
            ->mapWithKeys(fn ($login) => [mb_strtolower($login) => true])
            ->all();

        DB::table('users')
            ->select('id', 'name', 'email', 'login')
            ->orderBy('id')
            ->chunk(100, function ($users) use (&$used) {
                foreach ($users as $user) {
                    if (!empty($user->login)) {
                        continue;
                    }

                    $base = $this->loginBase($user->email ?: $user->name ?: ('user' . $user->id));
                    $login = $this->uniqueLogin($base, $used);
                    $used[mb_strtolower($login)] = true;

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['login' => $login]);
                }
            });
    }

    private function makeEmailNullable(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN email DROP NOT NULL');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
            return;
        }

        if ($driver === 'sqlite') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    private function loginBase(string $value): string
    {
        $value = explode('@', $value)[0];
        $value = mb_strtolower($value);
        $value = strtr($value, [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ]);
        $value = preg_replace('/[^a-z0-9]+/', '.', $value);
        $value = trim($value ?: '', '.');

        return $value !== '' ? mb_substr($value, 0, 32) : 'user';
    }

    private function uniqueLogin(string $base, array $used): string
    {
        $base = trim($base, '.');
        $base = $base !== '' ? $base : 'user';
        $candidate = $base;

        if (empty($used[mb_strtolower($candidate)])) {
            return $candidate;
        }

        for ($i = 1; $i < 1000; $i++) {
            $suffix = (string) $i;
            $candidate = mb_substr($base, 0, 32 - mb_strlen($suffix) - 1) . '.' . $suffix;

            if (empty($used[mb_strtolower($candidate)])) {
                return $candidate;
            }
        }

        return 'user.' . random_int(100000, 999999);
    }
};
