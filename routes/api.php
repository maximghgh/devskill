<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\CourseCommentController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\FinalTestController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\FinalTestResultController;
use App\Http\Controllers\TaskSubmissionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseQrController;
use App\Http\Controllers\GroupController;
//проверка задания и отправка задания
Route::get('chapter/{chapter}/my-submission', [TaskSubmissionController::class, 'mySubmission']);
Route::post('/submitTask', [TaskSubmissionController::class, 'store']);               // студент отправляет
Route::get('/submissions', [TaskSubmissionController::class, 'index']);               // список (для кабинета студента/препода)
Route::post('/submissions/{submission}/review', [TaskSubmissionController::class, 'review']); // проверка преподом
Route::get('/submissions/{submission}/download', [TaskSubmissionController::class, 'download']); // опционально
Route::get('/students/{courseId}', [StudentController::class, 'listByCourse']);
Route::get('/course/{courseId}/student/{studentId}/submissions', [TaskSubmissionController::class, 'getByStudent']);
Route::put('/submissions/{id}', [TaskSubmissionController::class, 'update']);


Route::post('/coursestaks', [CourseController::class, 'getCoursesByTeacher']);
Route::get('/students/{courseId}', [CourseController::class, 'showStudents']);

Route::post('/chapters/{chapter}/complete', [ChapterController::class,'complete']);
Route::get('/courses/{course}/final-test', [FinalTestController::class,'show']);
Route::get('final-test/{courseId}', [FinalTestController::class, 'show'])
     ->whereNumber('courseId');
Route::post('/final-tests/{test}', [FinalTestController::class,'submit']);
Route::post('/final-test/{course}/submit',[FinalTestController::class, 'check']
);
Route::get('/final-test/{course}/results',[FinalTestController::class, 'results']
);
Route::get(
    '/teacher/{teacher}/students-results',
    [TeacherController::class, 'studentsResults']
);
Route::get('/final-test-results', [FinalTestResultController::class, 'index']);
Route::get('final-test/status', [FinalTestController::class, 'status']);
Route::get(
  '/final-test-results/{id}',
  [FinalTestResultController::class, 'show']
);
Route::prefix('admin')->group(function(){
    // получить итоговый тест
    Route::get(
      'course/{course}/final-test',
      [FinalTestController::class, 'show']
    );
    // создать
    Route::post(
      'course/{course}/final-test',
      [FinalTestController::class, 'store']
    );
    // обновить
    Route::get(
        'course/{course}/final-test/data',
        [FinalTestController::class, 'getByCourse']
    );
    // удалить тест
    Route::delete(
        'course/{course}/final-test/data',
        [FinalTestController::class, 'destroyByCourse']
    );
    // обновить тест
    Route::match(
      ['put','patch'],
      'course/{course}/final-test/data',
      [FinalTestController::class, 'updateByCourse']
    );
});
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/chapters/stats', [ChapterController::class, 'getStats']);
Route::get('/purchases', [PurchaseController::class, 'index']);
Route::get('/consultations', [ConsultationController::class, 'index']);
Route::post('/consultations/{id}/complete', [ConsultationController::class, 'complete']);
Route::post('/consultations/{id}/status', [ConsultationController::class, 'updateStatus']);

Route::get('/topics/{topic}/task-count', [ChapterController::class, 'taskCount']);
Route::get('/courses/{course}/task-count', [CourseController::class, 'taskCount']);
// прогресс пользователя
Route::get('/course/{courseId}/topics', [CourseController::class, 'getTopicsWithProgress']);
Route::post('/chapters/{chapter}/complete', [ChapterController::class, 'completeChapter']);

Route::post('courses/{course}/certificate', [CertificateController::class, 'generate']);

Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::prefix('admin')->name('admin.')->group(function () {
    // Создание курса (API)
    Route::post('/courses', [CourseController::class, 'store'])
         ->name('courses.store');
});

Route::get('/chapter/{id}', [ChapterController::class, 'showteach'])
     ->name('api.chapter.show');

// Получение id пользователя 
Route::post('/users/by-ids', [UserController::class, 'getByIds']);
Route::get('/users/{id}', [UserController::class, 'show']);

// Маршрут для сохранения картинки
Route::post('/uploadFile', [ImageUploadController::class, 'store']);

// Маршрут для сохранения курса
Route::post('/courses', [CourseController::class, 'store']);

// Маршрут для получения конкретного курса
Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::post('/courses/{id}', [CourseController::class, 'update']); 

// Должен быть маршрут для получения курсов
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses', [CourseController::class, 'category']);

Route::get('/courses/{course}/qr', [CourseQrController::class, 'show']);

// Удаление курса 
Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

Route::get('/users', [UserController::class, 'index']);
Route::post('/profile', [ProfileController::class, 'updateProfile']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// добаваление авы
Route::post('/users/{id}/photo', [UserController::class, 'updateAvatar']);

// Верефикация по email
Route::get('/emails/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verify.email');
// Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])
//     ->name('verify.email');


Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
// Route::post('/resend-link', [AuthController::class, 'resendEmail'])
//     ->name('resend.link');


// Создание пользователя и редактирование пользователя
Route::post('/create-user', [AuthController::class, 'createUser']);
Route::post('/register', [AuthController::class, 'register']);

// Добавление категорий обучения(языков программирования)
Route::post('/languages', [LanguageController::class, 'store']);
Route::get('/languages', [LanguageController::class, 'index']);
// Обновление языка программирования
Route::patch('/languages/{id}', [LanguageController::class, 'update']);
// Удаление языка программирования
Route::delete('/languages/{id}', [LanguageController::class, 'destroy']);

// Добавление категорий направления
Route::post('/directions', [DirectionController::class, 'store']);
Route::get('/directions', [DirectionController::class, 'index']);
// Обновление направления
Route::patch('/directions/{id}', [DirectionController::class, 'update']);
// Удаление направления
Route::delete('/directions/{id}', [DirectionController::class, 'destroy']);

// Получить комментарии для новости {id}
Route::get('comments/news/{id}', [CommentController::class, 'index']);
Route::get('/news/{news}/comments', [CommentController::class, 'adminindex']);
Route::post('/news/{news}/comments', [CommentController::class, 'adminstore']);

// Добавление частых вопросов
Route::post('/faqs', [FaqController::class, 'store']);
Route::get('/faqs', [FaqController::class, 'index']);

Route::apiResource('faqs', FaqController::class)
     ->only(['update','destroy']);

// routes/api.php
Route::get('/stats', [App\Http\Controllers\StatsController::class, 'index']);


Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{id}', [NewsController::class, 'show']);
Route::post('/news', [NewsController::class, 'store']);
Route::patch('/news/{id}', [NewsController::class, 'update']);
Route::delete('/news/{id}', [NewsController::class, 'destroy']);

//комментарии у курсов
Route::get('/courses/{course}/comments', [CourseCommentController::class, 'index']);
Route::post('/courses/{course}/comments', [CourseCommentController::class, 'store']);
Route::patch('/course-comments/{comment}', [CourseCommentController::class, 'update']);
Route::delete('/course-comments/{comment}', [CourseCommentController::class, 'destroy']);

Route::post('/courses/{course}/comments/{comment}/like', [CourseCommentController::class, 'likeComment']);
Route::post('/courses/{course}/comments/{comment}/dislike', [CourseCommentController::class, 'dislikeComment']);


Route::get('/news-single/{id}', [NewsController::class, 'show']);

// Маршруты для комментариев
Route::post('/comments', [CommentController::class, 'store']);
Route::put('/comments/{id}', [CommentController::class, 'update']);
Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

Route::post('/comments/{id}/like', [CommentController::class, 'like']);
Route::post('/comments/{id}/dislike', [CommentController::class, 'dislike']);
Route::post('/comments/{id}/unlike', [CommentController::class, 'unlike']);
Route::post('/comments/{id}/undislike', [CommentController::class, 'undislike']);



Route::get('/user/{id}/purchased-courses', [UserController::class, 'getPurchasedCourses']);

Route::middleware('auth:sanctum')->get('/user/purchased-courses', [UserController::class, 'getPurchasedCourses']);
    
Route::post('/{course}/purchase', [PurchaseController::class, 'store'])->name('purchases.store');
Route::post('/{course}/consultation', [ConsultationController::class, 'store'])->name('consultations.store');

Route::prefix('admin/course/{course}')->group(function () {
    // Получить список тем курса
    Route::get('/topics', [TopicController::class, 'index'])->name('admin.topics.index');
    // Создать новую тему для курса
    Route::post('/topics', [TopicController::class, 'store'])->name('admin.topics.store');

    Route::get('/qr', [CourseQrController::class, 'show']);
    Route::post('/qr', [CourseQrController::class, 'store']);
    Route::delete('/qr', [CourseQrController::class, 'destroy']);

    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::get('/groups/{group}', [GroupController::class, 'show']);
    Route::patch('/groups/{group}', [GroupController::class, 'update']);
    Route::delete('/groups/{group}', [GroupController::class, 'destroy']);
});

Route::prefix('admin/topics')->group(function () {
    // PATCH /admin/topics/{topic}
    Route::patch('/{topic}', [TopicController::class, 'update']);
    // DELETE /admin/topics/{topic}
    Route::delete('/{topic}', [TopicController::class, 'destroy']);
});

Route::prefix('admin/topic/{topicId}')->group(function () {
    Route::put('chapters/{chapterId}', [ChapterController::class, 'update']);
    Route::delete('chapters/{chapterId}', [ChapterController::class, 'destroy']);
});

Route::get('/chapters/{chapter}/final-test', [ChapterController::class, 'finalTest']);
