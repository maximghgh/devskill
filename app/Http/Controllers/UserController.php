<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserAvatarRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\CourseResource;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function index()
    {
        // Получаем всех пользователей
        $users = User::select('id', 'name', 'login', 'email', 'phone', 'country', 'role', 'birthday', 'created_at', 'photo', 'position','inn')
                     ->orderBy('id', 'asc')
                     ->get();

        // Возвращаем JSON
        return response()->json(UserResource::collection($users));
    }
    public function show($id)
    {
        // Ищем пользователя по ID или возвращаем 404, если не найден
        $user = User::findOrFail($id);
        return response()->json(new UserResource($user));
    }
    public function getByIds(Request $request)
    {
        // Массив ID, которые передаёт фронтенд
        $ids = $request->input('ids', []);
        // Получаем пользователей с этими ID
        $users = User::whereIn('id', $ids)->get();
        return response()->json(UserResource::collection($users));
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
            'courses' => CourseResource::collection($courses),
        ]);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        // Валидируем входные данные
        $validated = $request->validated();

        // Ищем пользователя по ID
        $user = User::findOrFail($id);

        // Обновляем поля пользователя
        // Обновляем все поля разом
        $user->update($validated);

        // Сохраняем изменения в базе
        $user->save();

        return response()->json([
            'success' => true,
            'user'    => new UserResource($user),
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $extension = mb_strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['xlsx', 'csv', 'txt'], true)) {
                        $fail('Поддерживаются только файлы .xlsx и .csv.');
                    }
                },
            ],
        ]);

        try {
            $rawRows = $this->readSpreadsheetRows($request->file('file'));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        if (count($rawRows) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'В файле не найдено строк с пользователями.',
            ], 422);
        }

        $headerMap = $this->detectHeaderMap($rawRows[0]);
        $startIndex = $headerMap ? 1 : 0;
        $usedLogins = User::whereNotNull('login')
            ->pluck('login')
            ->mapWithKeys(fn ($login) => [mb_strtolower($login) => true])
            ->all();
        $usedEmails = User::whereNotNull('email')
            ->pluck('email')
            ->mapWithKeys(fn ($email) => [mb_strtolower($email) => true])
            ->all();

        $created = [];
        $skipped = [];

        for ($i = $startIndex; $i < count($rawRows); $i++) {
            $rowNumber = $i + 1;
            $row = $headerMap
                ? $this->rowByHeader($rawRows[$i], $headerMap)
                : $this->rowWithoutHeader($rawRows[$i]);

            if ($this->isImportRowEmpty($row)) {
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            $email = $this->normalizeEmail((string) ($row['email'] ?? ''));
            $errors = [];

            if ($name === '') {
                $errors[] = 'Не указано ФИО.';
            }

            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Некорректная почта.';
            } elseif ($email && !empty($usedEmails[mb_strtolower($email)])) {
                $errors[] = 'Почта уже используется.';
            }

            if ($errors) {
                $skipped[] = [
                    'row' => $rowNumber,
                    'name' => $name,
                    'errors' => $errors,
                ];
                continue;
            }

            $loginBase = $this->sanitizeLogin((string) ($row['login'] ?? ''));
            if ($loginBase === '') {
                $loginBase = $this->loginBaseFromName($name);
            }

            $login = $this->uniqueLogin($loginBase, $usedLogins);
            $plainPassword = trim((string) ($row['password'] ?? ''));
            if ($plainPassword === '') {
                $plainPassword = $this->generatePassword();
            }

            try {
                $user = User::create([
                    'name' => $name,
                    'login' => $login,
                    'email' => $email ?: null,
                    'password' => Hash::make($plainPassword),
                    'role' => $this->normalizeRole((string) ($row['role'] ?? '')),
                    'phone' => trim((string) ($row['phone'] ?? '')) ?: null,
                    'birthday' => $this->normalizeDate((string) ($row['birthday'] ?? '')),
                    'country' => trim((string) ($row['country'] ?? '')) ?: null,
                    'position' => trim((string) ($row['position'] ?? '')) ?: null,
                ]);

                $usedLogins[mb_strtolower($login)] = true;
                if ($email) {
                    $usedEmails[mb_strtolower($email)] = true;
                }

                $created[] = [
                    'user' => (new UserResource($user->fresh()))->resolve($request),
                    'name' => $user->name,
                    'login' => $login,
                    'password' => $plainPassword,
                    'email' => $email,
                ];
            } catch (\Throwable $e) {
                $skipped[] = [
                    'row' => $rowNumber,
                    'name' => $name,
                    'errors' => ['Не удалось создать пользователя: ' . $e->getMessage()],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'created_count' => count($created),
            'skipped_count' => count($skipped),
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    public function exportCredentials(Request $request)
    {
        $validated = $request->validate([
            'credentials' => 'required|array|min:1',
            'credentials.*.name' => 'nullable|string|max:255',
            'credentials.*.login' => 'required|string|max:255',
            'credentials.*.password' => 'required|string|max:255',
            'credentials.*.email' => 'nullable|email|max:255',
        ]);

        try {
            $path = $this->createCredentialsXlsx($validated['credentials']);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось сформировать Excel-файл: ' . $e->getMessage(),
            ], 500);
        }

        return response()
            ->download($path, 'users_credentials.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    public function updateAvatar(UpdateUserAvatarRequest $request, $id)
    {
        // Находим пользователя по ID
        $user = User::findOrFail($id);

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
            'user'    => new UserResource($user),
        ]);
    }

    private function readSpreadsheetRows($file): array
    {
        $extension = mb_strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->readCsvRows($file->getRealPath());
        }

        if ($extension === 'xlsx') {
            return $this->readXlsxRows($file->getRealPath());
        }

        throw new \RuntimeException('Поддерживаются только файлы .xlsx и .csv.');
    }

    private function createCredentialsXlsx(array $credentials): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('На сервере недоступен ZipArchive для создания .xlsx.');
        }

        $dir = storage_path('app/temp');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir . '/users_credentials_' . uniqid('', true) . '.xlsx';
        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Не удалось создать временный Excel-файл.');
        }

        $headers = ['ФИО', 'Логин', 'Пароль', 'E-mail'];
        $rows = [$headers];

        foreach ($credentials as $item) {
            $rows[] = [
                (string) ($item['name'] ?? ''),
                (string) ($item['login'] ?? ''),
                (string) ($item['password'] ?? ''),
                (string) ($item['email'] ?? ''),
            ];
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->xlsxStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxWorksheetXml($rows));
        $zip->close();

        return $path;
    }

    private function xlsxWorksheetXml(array $rows): string
    {
        $sheetData = '';

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $cells = '';

            foreach ($row as $columnIndex => $value) {
                $cell = $this->xlsxColumnName($columnIndex + 1) . $excelRow;
                $style = $rowIndex === 0 ? ' s="1"' : '';
                $cells .= '<c r="' . $cell . '"' . $style . ' t="inlineStr"><is><t>'
                    . $this->xml($value)
                    . '</t></is></c>';
            }

            $sheetData .= '<row r="' . $excelRow . '">' . $cells . '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols>'
            . '<col min="1" max="1" width="32" customWidth="1"/>'
            . '<col min="2" max="2" width="22" customWidth="1"/>'
            . '<col min="3" max="3" width="18" customWidth="1"/>'
            . '<col min="4" max="4" width="32" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . $sheetData . '</sheetData>'
            . '</worksheet>';
    }

    private function xlsxColumnName(int $number): string
    {
        $name = '';

        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)) . $name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function xlsxContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function xlsxRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function xlsxWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Логины и пароли" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function xlsxWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function xlsxStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF41328F"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="1" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
            . '</styleSheet>';
    }

    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (!$handle) {
            throw new \RuntimeException('Не удалось открыть CSV-файл.');
        }

        $firstLine = fgets($handle) ?: '';
        rewind($handle);
        $delimiter = $this->detectCsvDelimiter($firstLine);
        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = array_map(fn ($cell) => trim((string) $cell), $row);
            if (!$this->isRawRowEmpty($row)) {
                $rows[] = $row;
            }
        }

        fclose($handle);

        return $rows;
    }

    private function readXlsxRows(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('На сервере недоступен ZipArchive для чтения .xlsx.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Не удалось открыть .xlsx-файл.');
        }

        $sharedStrings = $this->readXlsxSharedStrings($zip);
        $sheetPath = $this->firstWorksheetPath($zip);
        $sheetXml = $zip->getFromName($sheetPath);

        if ($sheetXml === false) {
            $zip->close();
            throw new \RuntimeException('В .xlsx-файле не найден первый лист.');
        }

        $sheet = simplexml_load_string($sheetXml);
        if ($sheet === false) {
            $zip->close();
            throw new \RuntimeException('Не удалось прочитать первый лист .xlsx.');
        }

        $namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $rows = [];

        foreach ($sheet->children($namespace)->sheetData->children($namespace)->row as $row) {
            $values = [];
            $nextIndex = 0;

            foreach ($row->children($namespace)->c as $cell) {
                $attributes = $cell->attributes();
                $reference = (string) ($attributes['r'] ?? '');
                $columnIndex = $reference ? $this->columnIndexFromReference($reference) : $nextIndex;
                $values[$columnIndex] = $this->xlsxCellValue($cell, $sharedStrings, $namespace);
                $nextIndex = $columnIndex + 1;
            }

            if (!$values) {
                continue;
            }

            ksort($values);
            $lastIndex = max(array_keys($values));
            $normalized = [];
            for ($i = 0; $i <= $lastIndex; $i++) {
                $normalized[] = trim((string) ($values[$i] ?? ''));
            }

            if (!$this->isRawRowEmpty($normalized)) {
                $rows[] = $normalized;
            }
        }

        $zip->close();

        return $rows;
    }

    private function readXlsxSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $strings = [];
        $shared = simplexml_load_string($xml);
        if ($shared === false) {
            return [];
        }

        $namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        foreach ($shared->children($namespace)->si as $item) {
            $parts = [];
            foreach ($item->xpath('.//*[local-name()="t"]') ?: [] as $text) {
                $parts[] = (string) $text;
            }
            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function firstWorksheetPath(\ZipArchive $zip): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml !== false && $relsXml !== false) {
            $workbook = simplexml_load_string($workbookXml);
            $rels = simplexml_load_string($relsXml);
            $spreadsheetNamespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
            $relationshipNamespace = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
            $packageNamespace = 'http://schemas.openxmlformats.org/package/2006/relationships';

            if ($workbook === false || $rels === false) {
                return 'xl/worksheets/sheet1.xml';
            }

            $firstSheet = $workbook->children($spreadsheetNamespace)->sheets->children($spreadsheetNamespace)->sheet[0] ?? null;
            $relationshipId = $firstSheet ? (string) $firstSheet->attributes($relationshipNamespace)['id'] : '';

            if ($relationshipId && $rels) {
                foreach ($rels->children($packageNamespace)->Relationship as $relationship) {
                    if ((string) $relationship['Id'] === $relationshipId) {
                        $target = (string) $relationship['Target'];
                        return str_starts_with($target, '/')
                            ? ltrim($target, '/')
                            : 'xl/' . ltrim($target, '/');
                    }
                }
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function xlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings, string $namespace): string
    {
        $attributes = $cell->attributes();
        $type = (string) ($attributes['t'] ?? '');
        $children = $cell->children($namespace);

        if ($type === 's') {
            $index = isset($children->v) ? (int) $children->v : null;
            return $index !== null ? (string) ($sharedStrings[$index] ?? '') : '';
        }

        if ($type === 'inlineStr') {
            $parts = [];
            foreach ($children->is->xpath('.//*[local-name()="t"]') ?: [] as $text) {
                $parts[] = (string) $text;
            }
            return implode('', $parts);
        }

        return isset($children->v) ? (string) $children->v : '';
    }

    private function columnIndexFromReference(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference));
        $index = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    private function detectCsvDelimiter(string $line): string
    {
        $delimiters = [',', ';', "\t"];
        $bestDelimiter = ';';
        $bestCount = 0;

        foreach ($delimiters as $delimiter) {
            $count = substr_count($line, $delimiter);
            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    private function detectHeaderMap(array $row): ?array
    {
        $aliases = $this->headerAliases();
        $map = [];

        foreach ($row as $index => $cell) {
            $header = $this->normalizeHeader((string) $cell);
            if ($header === '') {
                continue;
            }

            foreach ($aliases as $field => $fieldAliases) {
                if (in_array($header, $fieldAliases, true)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        return $map ? $map : null;
    }

    private function headerAliases(): array
    {
        return [
            'name' => ['фио', 'ф и о', 'фамилия имя отчество', 'полное имя', 'ученик', 'учащийся', 'студент', 'name', 'full name', 'fio'],
            'last_name' => ['фамилия', 'surname', 'last name'],
            'first_name' => ['имя', 'first name'],
            'middle_name' => ['отчество', 'middle name'],
            'login' => ['логин', 'login', 'username', 'user name'],
            'password' => ['пароль', 'password'],
            'email' => ['почта', 'email', 'e mail', 'электронная почта'],
            'phone' => ['телефон', 'phone'],
            'birthday' => ['дата рождения', 'birthday', 'birth date'],
            'country' => ['страна', 'country'],
            'role' => ['роль', 'role'],
            'position' => ['должность', 'position'],
        ];
    }

    private function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace('ё', 'е', $value);
        $value = preg_replace('/[^a-zа-я0-9]+/u', ' ', $value);

        return trim(preg_replace('/\s+/u', ' ', $value ?: ''));
    }

    private function rowByHeader(array $cells, array $headerMap): array
    {
        $get = fn (string $field) => isset($headerMap[$field])
            ? trim((string) ($cells[$headerMap[$field]] ?? ''))
            : '';

        $name = $get('name');
        if ($name === '') {
            $name = trim(implode(' ', array_filter([
                $get('last_name'),
                $get('first_name'),
                $get('middle_name'),
            ])));
        }

        return [
            'name' => $name,
            'login' => $get('login'),
            'password' => $get('password'),
            'email' => $get('email'),
            'phone' => $get('phone'),
            'birthday' => $get('birthday'),
            'country' => $get('country'),
            'role' => $get('role'),
            'position' => $get('position'),
        ];
    }

    private function rowWithoutHeader(array $cells): array
    {
        $secondCell = trim((string) ($cells[1] ?? ''));
        $secondCellIsEmail = filter_var($secondCell, FILTER_VALIDATE_EMAIL);

        return [
            'name' => trim((string) ($cells[0] ?? '')),
            'email' => $secondCellIsEmail ? $secondCell : '',
            'phone' => $secondCellIsEmail ? trim((string) ($cells[2] ?? '')) : $secondCell,
            'birthday' => trim((string) ($cells[3] ?? '')),
            'role' => trim((string) ($cells[4] ?? '')),
        ];
    }

    private function isRawRowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function isImportRowEmpty(array $row): bool
    {
        return $this->isRawRowEmpty(array_values($row));
    }

    private function normalizeEmail(string $email): ?string
    {
        $email = mb_strtolower(trim($email));

        return $email !== '' ? $email : null;
    }

    private function normalizeRole(string $role): int
    {
        $role = $this->normalizeHeader($role);

        return match ($role) {
            '4', 'родитель', 'parent' => 4,
            '3', 'админ', 'администратор', 'admin' => 3,
            '2', 'преподаватель', 'teacher' => 2,
            default => 1,
        };
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $days = (int) $value;
            if ($days > 20000 && $days < 80000) {
                return (new \DateTimeImmutable('1899-12-30'))
                    ->modify('+' . $days . ' days')
                    ->format('Y-m-d');
            }
        }

        foreach (['Y-m-d', 'd.m.Y', 'd/m/Y', 'd-m-Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof \DateTimeImmutable) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function loginBaseFromName(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $base = trim(($parts[0] ?? 'user') . '.' . ($parts[1] ?? ''), '.');

        return $this->sanitizeLogin($base) ?: 'user';
    }

    private function sanitizeLogin(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ]);
        $value = preg_replace('/[^a-z0-9._-]+/', '.', $value);
        $value = trim($value ?: '', '.-_');

        return mb_substr($value, 0, 32);
    }

    private function uniqueLogin(string $base, array $usedLogins): string
    {
        $base = $this->sanitizeLogin($base) ?: 'user';
        $candidate = $base;

        if (empty($usedLogins[mb_strtolower($candidate)])) {
            return $candidate;
        }

        for ($attempt = 0; $attempt < 500; $attempt++) {
            $suffix = (string) random_int(1000, 9999);
            $candidate = mb_substr($base, 0, 27) . '.' . $suffix;

            if (empty($usedLogins[mb_strtolower($candidate)])) {
                return $candidate;
            }
        }

        return 'user.' . random_int(100000, 999999);
    }

    private function generatePassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $password = 'Dev-';

        for ($i = 0; $i < 6; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }

}
