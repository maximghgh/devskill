<?php

namespace App\Http\Controllers;

use App\Models\Course; 
use App\Models\Topic; 
use App\Models\UserChapterProgress; 
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\TopicResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Language;
use App\Support\CourseDifficulty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    // Базовый запрос курса: сразу считает кол-во тем и число учеников (уникальных покупателей).
    private function courseSummaryQuery(): Builder
    {
        return Course::query()
            ->withCount('topics')
            ->withCount([
                'purchases as students_count' => function ($query) {
                    $query->select(DB::raw('count(distinct user_id)'));
                },
            ]);
    }

    // Приводит поле «преподаватели» (строка JSON / массив / объекты) к массиву уникальных числовых id.
    private function normalizeTeacherIds(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        if ($value === null || $value === '') {
            return [];
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $teacherIds = array_map(function ($teacher) {
            if (is_array($teacher)) {
                $teacher = $teacher['id'] ?? null;
            }

            if ($teacher === null || $teacher === '') {
                return null;
            }

            return (int) $teacher;
        }, $value);

        return array_values(array_unique(array_filter($teacherIds, fn ($id) => $id > 0)));
    }

    // Даты начала/конца: берёт из запроса, а если не переданы — оставляет прежние (при обновлении).
    private function resolveCourseDates(array $data, ?Course $course = null): array
    {
        $startDateProvided = array_key_exists('start_date', $data);
        $endDateProvided = array_key_exists('end_date', $data);

        return [
            'start_date' => $startDateProvided ? ($data['start_date'] ?: null) : $course?->start_date,
            'end_date' => $endDateProvided ? ($data['end_date'] ?: null) : $course?->end_date,
        ];
    }

    // Транслитерация русского текста в латиницу (нужна для slug).
    private function transliterateRu(string $value): string
    {
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya', 'ъ' => '', 'ь' => '',
        ];

        $value = mb_strtolower($value, 'UTF-8');

        return strtr($value, $map);
    }

    // Делает уникальный slug из названия курса (при совпадении добавляет -2, -3 и т.д.).
    private function makeSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name, '-', 'ru');
        if ($base === '') {
            $base = Str::slug($this->transliterateRu($name), '-');
        }
        if ($base === '') {
            $base = 'course';
        }

        $slug = $base;
        $i = 2;

        while (
            Course::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '<>', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    // Сохраняет загруженный файл и возвращает путь вида "storage/...". keepName=true — имя = метка времени + оригинал (для PDF).
    private function storeUpload(\Illuminate\Http\UploadedFile $file, string $folder, bool $keepName = false): string
    {
        $path = $keepName
            ? $file->storeAs("public/$folder", time() . '_' . $file->getClientOriginalName())
            : $file->store("public/$folder");

        return str_replace('public/', 'storage/', $path);
    }

    // Заменяет PDF курса: удаляет старый файл и кладёт новый в public/storage/pdfs, возвращает новый путь.
    private function replaceCoursePdf(\Illuminate\Http\UploadedFile $file, ?string $oldPath): string
    {
        if ($oldPath && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }

        $filename    = time() . '_' . $file->getClientOriginalName();
        $destination = public_path('storage/pdfs');
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $file->move($destination, $filename);

        return 'storage/pdfs/' . $filename;
    }

    // Собирает поля запроса в snake_case-массив для сохранения курса (используется в store).
    private function buildCourseData(array $data, array $dates): array
    {
        return [
            'card_title'           => $data['cardTitle'] ?? null,
            'course_name'          => $data['courseName'],
            'price'                => $data['price'],
            'duration'             => $data['duration'],
            'description'          => $data['description'] ?? null,
            'hours'                => $data['hours'],
            'simulators'           => $data['simulators'],
            'difficulty'           => $data['difficulty'],
            'editor_data'          => $data['editorData'] ?? null,
            'teachers'             => $data['teachers'] ?: null,
            'language'             => $data['language'],
            'direction'            => $data['direction'] ?? null,
            'upgradequalification' => $data['upgradequalification'] ?? null,
            'card_image'           => $data['card_image'] ?? null,
            'description_image'    => $data['description_image'] ?? null,
            'pdf_path'             => $data['pdf_path'] ?? null,
            'start_date'           => $dates['start_date'],
            'end_date'             => $dates['end_date'],
        ];
    }

    // Создание курса: валидация, разбор JSON-полей, загрузка файлов (обложка/описание/PDF), запись в БД.
    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();

        // JSON-строки -> массивы, преподаватели -> массив id, даты
        $data['language']   = json_decode($request->input('language'), true);
        $data['editorData'] = json_decode($data['editorData'], true);
        $data['teachers']   = $this->normalizeTeacherIds($data['teachers'] ?? null);
        $dates = $this->resolveCourseDates($data);

        // Загрузка файлов
        if ($request->hasFile('cardImage')) {
            $data['card_image'] = $this->storeUpload($request->file('cardImage'), 'cards');
        }
        if ($request->hasFile('descriptionImage')) {
            $data['description_image'] = $this->storeUpload($request->file('descriptionImage'), 'descriptions');
        }
        if ($request->hasFile('pdf')) {
            $data['pdf_path'] = $this->storeUpload($request->file('pdf'), 'pdfs', keepName: true);
        }

        // Сборка полей + уникальный slug -> запись в БД
        $dataToSave = $this->buildCourseData($data, $dates);
        $dataToSave['slug'] = $this->makeSlug($data['courseName'] ?? ($data['cardTitle'] ?? 'course'));

        $course = Course::create($dataToSave);

        return response()->json([
            'message'      => 'Курс успешно создан',
            'course'       => new CourseResource($course),
            'redirect_url' => route('admin.course.show', ['id' => $course->id]),
        ], 201);
    }
    // Обновление курса по id: перезапись полей и файлов (старый PDF удаляется).
    public function update(UpdateCourseRequest $request, $id)
    {
        $course = Course::findOrFail($id);
        $validated = $request->validated();

        // JSON-строки -> массивы, преподаватели -> массив id, даты
        if (!empty($validated['language'])) {
            $validated['language'] = json_decode($validated['language'], true);
        }
        $validated['teachers'] = $this->normalizeTeacherIds($validated['teachers'] ?? null);
        $dates = $this->resolveCourseDates($validated, $course);
        if ($request->has('editorData')) {
            $validated['editorData'] = json_decode($request->input('editorData'), true);
        }

        // Поля (частичное обновление: если поле не пришло — оставляем прежнее)
        $course->card_title  = $validated['cardTitle'] ?? null;
        $course->course_name = $validated['courseName'];
        $course->price       = $validated['price'];
        $course->duration    = $validated['duration'];
        $course->description = $validated['description'] ?? $course->description;
        $course->hours       = $validated['hours'] ?? $course->hours;
        $course->simulators  = $validated['simulators'] ?? $course->simulators;
        $course->difficulty  = $validated['difficulty'];
        $course->language    = $validated['language'];
        $course->start_date  = $dates['start_date'];
        $course->end_date    = $dates['end_date'];
        $course->upgradequalification = $validated['upgradequalification'];

        if (empty($course->slug)) {
            $course->slug = $this->makeSlug($validated['courseName'] ?? $course->course_name, $course->id);
        }
        if (array_key_exists('teachers', $validated)) {
            $course->teachers = $validated['teachers'] ?: null;
        }
        if (array_key_exists('direction', $validated)) {
            $course->direction = $validated['direction'];
        }
        if (isset($validated['editorData'])) {
            $course->editor_data = $validated['editorData'];
        }

        // Файлы: картинки — общий хелпер; PDF — с удалением старого
        if ($request->hasFile('cardImage')) {
            $course->card_image = $this->storeUpload($request->file('cardImage'), 'cards');
        }
        if ($request->hasFile('descriptionImage')) {
            $course->description_image = $this->storeUpload($request->file('descriptionImage'), 'descriptions');
        }
        if ($request->hasFile('pdf')) {
            $course->pdf_path = $this->replaceCoursePdf($request->file('pdf'), $course->pdf_path);
        }

        $course->save();

        return response()->json([
            'success' => true,
            'course'  => new CourseResource($course),
        ]);
    }

    // Удаление курса по id.
    public function destroy($id)
{
    // Ищем курс по id
    $course = Course::findOrFail($id);

    // Удаляем курс
    $course->delete();

    // Возвращаем ответ
    return response()->json([
        'success' => true,
        'message' => 'Курс успешно удалён.'
    ]);
}

    // Один курс в JSON (с кол-вом тем/учеников); поле teachers разворачивается из JSON.
    public function show($id)
    {
        $course = $this->courseSummaryQuery()->findOrFail($id);
        // $course = Course::findOrFail($id); 
        // // Если записи нет, Laravel автоматически вернет 404
        // if (is_string($course->teachers)) {
        //     $course->teachers = json_decode($course->teachers, true);
        // }
        if (is_string($course->teachers)) {
            $course->teachers = json_decode($course->teachers, true);
        }
        return response()->json(new CourseResource($course));
    }

    // Публичная страница курса по slug (Blade + SEO-мета: title, description, og-image).
    public function showBySlug(string $slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();

        $title = $course->course_name ?: $course->card_title;
        $description = $course->description ? strip_tags($course->description) : '';
        $description = $description ?: ('Курс ' . $title . '.');
        $description = Str::limit($description, 160, '');

        $canonical = url('/courses/' . $course->slug);
        $ogImage = $course->description_image ?: $course->card_image;
        $ogImage = $ogImage ? asset($ogImage) : null;

        return view('course', [
            'course' => $course,
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'ogImage' => $ogImage,
        ]);
    }
    
    // Отдаёт Blade-страницу курса по id.
    public function showPage($id)
    {
        $course = Course::findOrFail($id);

        // Передаём данные в шаблон, например, resources/views/courses/show.blade.php
        return view('cpp', [
            'course' => $course,
        ]);
    }
    
    // Список всех курсов в JSON (с кол-вом тем и учеников).
    public function index()
    {
        return response()->json(
            CourseResource::collection($this->courseSummaryQuery()->orderBy('id')->get()),
            200
        );
    }
    // Каталог с фильтрами: язык, уровень сложности, направление, длительность.
    public function category(Request $request)
    {
        // Создаём query builder
        $query = $this->courseSummaryQuery();

        /**
         * 1) Фильтр по языкам (language)
         *    Предположим, что на фронте вы передаёте массив языков.
         *    Если language хранится в одном поле как строка (например, "Python" или "JavaScript"),
         *    используем whereIn. Если же у вас JSON в базе, логика будет иная.
         */
        if ($request->filled('languages')) {
            // Будет строка "1,2,3"
            $langsParam = $request->input('languages'); 
            // Превращаем её в массив: ['1', '2', '3']
            $langsArray = explode(',', $langsParam);
        
            // Если нужно привести значения к int (важно, если у вас в БД JSON-массив чисел):
            $langsArray = array_map('intval', $langsArray);
        
            // Применяем фильтр. Например, если в БД лежит JSON-массив чисел [1,2,3]:
            $query->where(function ($q) use ($langsArray) {
                foreach ($langsArray as $lang) {
                    $q->orWhereJsonContains('language', $lang);
                }
            });
        }
        
        
        /**
         * 2) Фильтр по уровню (difficulty)
         *    На фронте передаются актуальные значения уровней курса,
         *    а также допускаются legacy-коды для обратной совместимости.
         *    Если пользователь выбрал несколько уровней, то мы берём массив.
         */
        if ($request->has('difficulties')) {
            $diffs = array_values(array_intersect(
                (array) $request->input('difficulties'),
                CourseDifficulty::allowedValues()
            ));

            if (empty($diffs)) {
                return response()->json(CourseResource::collection([]), 200);
            }

            $query->whereIn('difficulty', $diffs);
        }

        /**
         * 3) Фильтр по направлению (direction)
         *    На фронте может быть выпадающий список. Если выбрано "Все направления",
         *    то параметр вообще не отправляем, либо передаём что-то вроде direction=all и пропускаем фильтр.
         */
        if ($request->has('direction') && $request->direction !== 'all') {
            $query->where('direction', $request->direction);
        }

        /**
         * 4) Фильтр по длительности (duration)
         *    На фронте пользователь вводит число от 1 до 24.
         *    Предположим, мы хотим отобрать все курсы, у которых duration <= введённому значению.
         *    Если в базе duration хранится как строка, приводим к int.
         */
        if ($request->filled('duration')) {
            $query->whereJsonContains('duration', (int) $request->duration);
        }
        

        // Получаем отфильтрованные курсы
        $courses = $query->get();

        return response()->json(CourseResource::collection($courses), 200);
    }

    // Blade-страница прохождения курса (курс + темы + главы).
    public function showCourseContent($courseId)
    {
        // Загружаем курс с его темами и главами (предполагается, что настроены связи)
        $course = Course::with(['topics.chapters'])->find($courseId);

        if (!$course) {
            abort(404, 'Курс не найден');
        }

        // Передаём данные в Blade-шаблон
        return view('content', ['course' => $course]);
    }
    // JSON: курс и его темы с главами (eager loading).
    public function getTopics($courseId)
    {
        // Загружаем курс вместе с его темами и главами (Eager Loading)
        // Если курс не найден, вернёт 404
        $course = Course::with('topics.chapters')->findOrFail($courseId);

        // Можно вернуть объект курса и отдельно список тем (с вложенными главами)
        return response()->json([
            'course' => new CourseResource($course),
            'topics' => TopicResource::collection($course->topics),
        ]);
    }
    // Темы и главы курса + флаг is_completed по прогрессу конкретного ученика (user_id).
    public function getTopicsWithProgress(Request $request, $courseId)
    {
        $userId = $request->input('user_id') ?? auth()->id();

        if (!$userId) {
            return response()->json([
                'message' => 'user_id is required'
            ], 400);
        }

        // Загружаем все темы и их главы (в порядке сортировки — важно для открытия по группе)
        $topics = Topic::where('course_id', $courseId)
            ->with('chapters')
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        // Определяем, в каких группах курса состоит ученик.
        // Набор открытых тем у групп разный — его задаёт преподаватель
        // на странице курса. Если групп несколько, берём объединение наборов.
        $groupIds = \App\Models\Group::where('course_id', $courseId)
            ->whereHas('students', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->pluck('id');

        // Ученик в группе — статус темы определяется её набором и
        // переопределяет глобальный статус. Не в группе — статус темы как есть.
        if ($groupIds->isNotEmpty()) {
            $openTopicIds = DB::table('group_topics')
                ->whereIn('group_id', $groupIds)
                ->pluck('topic_id')
                ->flip();

            foreach ($topics as $topic) {
                $topic->status = $openTopicIds->has($topic->id) ? 'активный' : 'закрыт';
            }
        }

        // Загружаем прогресс пользователя
        $progressRows = UserChapterProgress::where('user_id', $userId)
            ->whereIn('chapter_id', function($q) use ($courseId) {
                $q->select('id')
                ->from('chapters')
                ->whereIn('topic_id', function($q2) use ($courseId) {
                    $q2->select('id')->from('topics')->where('course_id', $courseId);
                });
            })
            ->get();

        // Получаем список идентификаторов пройденных глав
        $completedChapterIds = $progressRows->pluck('chapter_id')->unique();

        // Для каждой главы в темах выставляем флаг is_completed
        foreach ($topics as $topic) {
            foreach ($topic->chapters as $chapter) {
                // $chapter->setAttribute('is_completed', $completedChapterIds->contains($chapter->id));
                $chapter->is_completed = $completedChapterIds->contains($chapter->id);
            }
        }
             

        return response()->json([
            'topics' => TopicResource::collection($topics),
            'course' => new CourseResource($this->courseSummaryQuery()->findOrFail($courseId)),
        ]);
    }
    // Считает число заданий (глав с type=task) в курсе.
    public function taskCount($courseId)
    {
        // получаем все id тем данного курса
        $topicIds = \App\Models\Topic::where('course_id', $courseId)->pluck('id');

        // считаем все главы-«тренажёры» среди этих тем
        $count = \App\Models\Chapter::whereIn('topic_id', $topicIds)
                ->where('type', 'task')
                ->count();

        return response()->json([
        'taskCount' => $count
        ]);
    }
    // Курсы конкретного преподавателя (ищет teacher_id внутри JSON-поля teachers).
    public function getCoursesByTeacher(Request $request)
    {
        // Получаем teacher_id из тела POST-запроса
        $teacherId = $request->input('teacher_id');

        // Проверка, если teacher_id не передан
        if (!$teacherId) {
            return response()->json(['error' => 'Teacher ID is required'], 400);
        }

        try {
            // Получаем курсы, где массив teachers содержит указанный teacher_id
            $courses = $this->courseSummaryQuery()
                ->whereJsonContains('teachers', $teacherId)
                ->get();

            // Возвращаем найденные курсы
            return response()->json(CourseResource::collection($courses));
        } catch (\Exception $e) {
            // Логируем ошибку
            Log::error('Ошибка при получении курсов для преподавателя: ' . $e->getMessage());

            // Возвращаем ошибку
            return response()->json(['error' => 'Failed to fetch courses'], 500);
        }
    }

    // Список учеников курса (уникальные пользователи из покупок).
    public function showStudents($courseId)
    {
        $studentIds = Purchase::where('course_id', $courseId)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        if ($studentIds->isEmpty()) {
            return response()->json([]);
        }

        // Список учеников для преподавателя/админа — только необходимые поля,
        // без ИНН, телефона, паспортных JSON-данных и т.п.
        $students = \App\Models\User::whereIn('id', $studentIds)
            ->get(['id', 'name', 'email', 'photo']);

        return response()->json($students);
    }






}
