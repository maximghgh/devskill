<?php

namespace App\Http\Controllers;

use App\Models\Course; // Не забудьте создать модель Course
use App\Models\Topic; // Не забудьте создать модель Course
use App\Models\UserChapterProgress; // Не забудьте создать модель Course
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Language; // Ваша модель для языков

class CourseController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'cardTitle'           => 'nullable|string|max:255',
            'courseName'          => 'required|string|max:255',
            'price'               => 'required|numeric',
            'duration'            => 'required|string|max:255',
            'description'         => 'nullable|string',
            'hours'               => 'required|integer',
            'simulators'          => 'nullable|integer|min:0',
            'difficulty'          => 'required|string',
            'editorData'          => 'required', // данные Editor.js
            'teachers'            => 'nullable|json', // ожидаем JSON-строку с массивом ID преподавателей
            'language'            => 'required|string', // язык программирования
            'direction'           => 'nullable|string',
            'upgradequalification' => 'required|in:0,1',
            'cardImage'           => 'nullable|file|image|max:5120', // до 5 МБ
            'descriptionImage'    => 'nullable|file|image|max:5120',
            'pdf'                  => 'nullable|file|mimes:pdf|max:20480',
        ]);

        // $data['upgrade_qualification'] = $data['upgradeQualification'] === '1' ? true : false;
        // Преобразуем данные Editor.js из строки в массив, если нужно
        $data['language'] = json_decode($request->input('language'), true);

        $data['editorData'] = json_decode($data['editorData'], true);

        // Преобразуем поле teachers из JSON в массив, если оно передано
        if (isset($data['teachers'])) {
            $data['teachers'] = json_decode($data['teachers'], true);
        }

        // Обработка файла для карточки
        if ($request->hasFile('cardImage')) {
            $path = $request->file('cardImage')->store('public/cards');
            // сохраняем в $data['card_image']
            $data['card_image'] = str_replace('public/', 'storage/', $path);
        }

        // Обработка файла для описания
        if ($request->hasFile('descriptionImage')) {
            $path = $request->file('descriptionImage')->store('public/descriptions');
            $data['description_image'] = str_replace('public/', 'storage/', $path);
        }

        // Обработка файла для PDF
        if ($file = $request->file('pdf')) {

            // 1. Берём исходное имя
            $origName = $file->getClientOriginalName();          // «program.pdf»
        
            // 2. Чтобы избежать коллизий, добавим метку времени или id
            //    (можно заменить time() на Str::uuid() или courseName и т.п.)
            $filename = time() . '_' . $origName;                // «1714069531_program.pdf»
        
            // 3. Кладём с этим именем
            $storedPath = $file->storeAs('public/pdfs', $filename);
        
            // 4. Сохраняем путь в том же поле
            $data['pdf_path'] = str_replace('public/', 'storage/', $storedPath);
        }

        $dataToSave = [
            'card_title'         => $data['cardTitle'] ?? null,
            'course_name'        => $data['courseName'],
            'price'              => $data['price'],
            'duration'           => $data['duration'],
            'description'        => $data['description'] ?? null,
            'hours'              => $data['hours'],
            'simulators'         => $data['simulators'],
            'difficulty'         => $data['difficulty'],
            'editor_data'        => $data['editorData'] ?? null,
            'teachers'           => $data['teachers'] ?? null,
            'language'           => $data['language'],
            'direction'          => $data['direction'] ?? null,
            'upgradequalification'=> $data['upgradequalification'] ?? null,
            'card_image'         => $data['card_image'] ?? null,
            'description_image'  => $data['description_image'] ?? null,
            'pdf_path'             => $data['pdf_path'] ?? null, 
        ];
        
        // Создаём запись в БД
        $course = Course::create($dataToSave);
        
        return response()->json([
            'message' => 'Курс успешно создан',
            'course'  => $course,
            'redirect_url' => route('admin.course.show', ['id' => $course->id]),
        ], 201);
    }
    public function update(Request $request, $id)
    {
        // Если нужно отладить — посмотрите, какие поля реально приходят:
        // dd($request->all());

        $course = Course::findOrFail($id);

        // Валидация
        $validated = $request->validate([
            'cardTitle'           => 'nullable|string|max:255',
            'courseName'          => 'required|string|max:255',
            'price'               => 'required|numeric',
            'duration'            => 'required|string',
            'description'         => 'nullable|string',
            'hours'               => 'nullable|integer',
            'simulators'          => 'nullable|integer',
            'difficulty'          => 'required|string',
            'teachers'            => 'nullable|json',
            // Если раньше использовалось поле language, его можно заменить или оставить для обратной совместимости
            // 'language'          => 'required|string',
            'language'   => 'nullable|json', // ожидаем JSON-строку
            'selectedDirection'   => 'nullable|integer',
            'upgradequalification'=> 'required|in:0,1',
            'cardImage'           => 'nullable|file|image|max:5120',
            'descriptionImage'    => 'nullable|image|max:2048',
            'editorData'          => 'required', // Если нужно editorData
            'pdf' => 'nullable|file|mimes:pdf|max:20480',
        ]);


        if (!empty($validated['language'])) {
            $validated['language'] = json_decode($validated['language'], true);
        }
        
        $validated['teachers'] = json_decode($validated['teachers'], true);
        // Если editorData приходит как JSON-строка
        if ($request->has('editorData')) {
            $validated['editorData'] = json_decode($request->input('editorData'), true);
        }

        // Обновляем поля
        $course->card_title   = $validated['cardTitle'] ?? null;
        $course->course_name  = $validated['courseName'];
        $course->price        = $validated['price'];
        $course->duration     = $validated['duration'];
        $course->description  = $validated['description'] ?? $course->description;
        $course->hours        = $validated['hours'] ?? $course->hours;
        $course->simulators   = $validated['simulators'] ?? $course->simulators;
        $course->difficulty   = $validated['difficulty'];
        $course->language     = $validated['language'];

        // Если используется новое поле для языков (selectedLanguages), декодируем его и сохраняем в поле language
        // if (isset($validated['selectedLanguages'])) {
        //     $course->language = json_decode($validated['selectedLanguages'], true);
        // }
        if (array_key_exists('teachers', $validated)) {
            $course->teachers = array_values(array_unique($validated['teachers']));
            $course->save();  // ещё раз сохраняем, если нужен этот вариант
        }
        // Обновляем направление, если передано
        if (isset($validated['selectedDirection'])) {
            $course->direction = $validated['selectedDirection'];
        }

        // Обновляем поле повышения квалификации
        $course->upgradequalification = $validated['upgradequalification'];


        if (isset($validated['editorData'])) {
            $course->editor_data = $validated['editorData'];
        }

        // Обработка файлов, если есть
        if ($request->hasFile('cardImage')) {
            $path = $request->file('cardImage')->store('public/cards');
            // сохраняем в $data['card_image']
            $course->card_image = str_replace('public/', 'storage/', $path);
        }  
        if ($request->hasFile('descriptionImage')) {
            $path = $request->file('descriptionImage')->store('public/descriptions');
            $course->description_image = str_replace('public/', 'storage/', $path);
        }

        // PDF-файл
        if ($request->hasFile('pdf')) {
            // 1) Удаляем старый, если есть
            if ($course->pdf_path) {
                $oldPath = public_path($course->pdf_path);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        
            // 2) Берём файл из запроса
            $file     = $request->file('pdf');
            $origName = $file->getClientOriginalName();
            $filename = time() . '_' . $origName;
        
            // 3) Папка, куда сохраняем (storage/app/public/pdfs → public/storage/pdfs)
            $destination = public_path('storage/pdfs');
        
            // 4) Если папки нет — создаём
            if (! is_dir($destination)) {
                mkdir($destination, 0755, true);
            }
        
            // 5) Перемещаем файл
            $file->move($destination, $filename);
        
            // 6) Записываем путь в модель (относительно public/)
            $course->pdf_path = 'storage/pdfs/' . $filename;
        }

        $course->save();

        return response()->json([
            'success' => true,
            'course'  => $course,
        ]);
    }

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

    public function show($id)
    {
        $course = Course::withCount('topics')->findOrFail($id);
        // $course = Course::findOrFail($id); 
        // // Если записи нет, Laravel автоматически вернет 404
        // if (is_string($course->teachers)) {
        //     $course->teachers = json_decode($course->teachers, true);
        // }
        if (is_string($course->teachers)) {
            $course->teachers = json_decode($course->teachers, true);
        }
        return response()->json($course);
    }
    
    public function showPage($id)
    {
        $course = Course::findOrFail($id);

        // Передаём данные в шаблон, например, resources/views/courses/show.blade.php
        return view('сpp', [
            'course' => $course,
        ]);
    }
    
    public function index()
    {
        
        return response()->json(Course::all(), 200);
    }
    public function category(Request $request)
    {
        // Создаём query builder
        $query = Course::query();

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
         *    На фронте чекбоксы: basic, middle, advanced, mixed
         *    Если пользователь выбрал несколько уровней, то мы берём массив.
         */
        if ($request->has('difficulties')) {
            $diffs = $request->input('difficulties'); 
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

        return response()->json($courses, 200);
    }

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
    public function getTopics($courseId)
    {
        // Загружаем курс вместе с его темами и главами (Eager Loading)
        // Если курс не найден, вернёт 404
        $course = Course::with('topics.chapters')->findOrFail($courseId);

        // Можно вернуть объект курса и отдельно список тем (с вложенными главами)
        return response()->json([
            'course' => $course,
            'topics' => $course->topics // В каждой теме уже есть chapters
        ]);
    }
    public function getTopicsWithProgress(Request $request, $courseId)
    {
        $userId = $request->input('user_id') ?? auth()->id();

        if (!$userId) {
            return response()->json([
                'message' => 'user_id is required'
            ], 400);
        }

        // Загружаем все темы и их главы
        $topics = Topic::where('course_id', $courseId)
            ->with('chapters')
            ->get();

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
            'topics' => $topics,
            'course' => Course::find($courseId),
        ]);
    }
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
            $courses = Course::whereJsonContains('teachers', $teacherId)->get();

            // Возвращаем найденные курсы
            return response()->json($courses);
        } catch (\Exception $e) {
            // Логируем ошибку
            \Log::error('Ошибка при получении курсов для преподавателя: ' . $e->getMessage());

            // Возвращаем ошибку
            return response()->json(['error' => 'Failed to fetch courses'], 500);
        }
    }
    // В файле app/Http/Controllers/CourseController.php // Импортируем модель покупок

    public function showStudents($courseId)
    {
        // Получаем все покупки для данного курса
        $purchases = Purchase::where('course_id', $courseId)->get();

        // Если нет покупок, вернем пустой массив
        if ($purchases->isEmpty()) {
            return response()->json([]);
        }

        // Получаем уникальных пользователей для этого курса
        $students = $purchases->map(function ($purchase) {
            return $purchase->user; // Получаем пользователя для каждой покупки
        });

        // Возвращаем студентов в формате JSON
        return response()->json($students);
    }






}
