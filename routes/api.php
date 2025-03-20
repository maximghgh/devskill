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
Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::prefix('admin')->name('admin.')->group(function () {
    // Создание курса (API)
    Route::post('/courses', [CourseController::class, 'store'])
         ->name('courses.store');
});

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

// Удаление курса 
Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

Route::get('/users', [UserController::class, 'index']);
Route::post('/profile', [ProfileController::class, 'updateProfile']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Верефикация по email
Route::get('/emails/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verify.email');
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

// Создание пользователя и редактирование пользователя
Route::post('/create-user', [AuthController::class, 'createUser']);
Route::post('/register', [AuthController::class, 'register']);

// Добавление категорий обучения(языков программирования)
Route::post('/languages', [LanguageController::class, 'store']);
Route::get('/languages', [LanguageController::class, 'index']);

// Добавление категорий направления
Route::post('/directions', [DirectionController::class, 'store']);
Route::get('/directions', [DirectionController::class, 'index']);

// Добавление частых вопросов
Route::post('/faqs', [FaqController::class, 'store']);
Route::get('/faqs', [FaqController::class, 'index']);

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{id}', [NewsController::class, 'show']);
Route::post('/news', [NewsController::class, 'store']);
Route::put('/news/{id}', [NewsController::class, 'update']);
Route::delete('/news/{id}', [NewsController::class, 'destroy']);


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

Route::get('/course/{courseId}/topics', [CourseController::class, 'getTopics']);