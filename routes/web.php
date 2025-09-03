<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\FinalTestController;
use App\Models\Course;
use App\Http\Controllers\ForgotPasswordController;

Route::post('/password/email', [ForgotPasswordController::class, 'sendResetCode'])->name('password.email');
Route::post('/password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');


Route::get('/', function () {
    return view('welcome');
});

Route::view('/chapter/{id}', 'chapterview')   // resources/views/chapter.blade.php
     ->name('chapter.page');


Route::post('/certificate/generate', [CertificateController::class, 'generate'])
->name('certificate.generate');


Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/category-courses', function () {
    return view('category-courses');
})->name('category-courses');

Route::get('/catalog', function () {
    return view('catalog');
})->name('catalog');

Route::get('/content', function () {
    return view('content');
})->name('content');

Route::get('/cabinet', function () {
    return view('cabinet');
})->name('cabinet');

Route::get('/content/{courseId}', [CourseController::class, 'showCourseContent'])
    ->name('course.content');

Route::get('/admin/cabinet', function () {
    return view('admin.cabinet-admin');
})->name('admin.cabinet');;

Route::get('/admin/profile', function () {
    return view('admin.profile');
})->name('admin.profile');;

Route::get('/admin/statistics', function () {
    return view('admin.statistics');
})->name('admin.statistics');

Route::get('/admin/consultations', function () {
    return view('admin.consultations');
})->name('admin.consultations');

Route::get('/admin/statisticspurchase', function () {
    return view('admin.statisticspurchase');
})->name('admin.statisticspurchase');

Route::get('/profile', function () {
    return view('profile');
})->name('profile');

Route::get('/news-single', function () {
    return view('news-single');
})->name('news-single');

Route::get('/news-single/{id}', function ($id) {
    return view('news-single'); 
});

Route::get('/page-courses', function () {
    return view('page-courses');
})->name('page-courses');

Route::get('/admin/addcourse', function () {
    return view('admin.addcourse');
})->name('admin.addcourse');

Route::get('/taskstudent', function () {
    return view('taskstudent');
})->name('taskstudent');

Route::get('/studencourse/{course__id}', function () {
    return view('studencourse');
})->name('studencourse');

// Route::get('/studentsubmissions/{courseId}/{student_id}', function () {
//     return view('studentsubmissions');
// })->name('studentsubmissions');
Route::get('/studentsubmissions/{courseId}/{studentId}', function ($courseId, $studentId) {
    return view('studentsubmissions', compact('courseId','studentId'));
});

Route::get('/verify', function () {
    return view('verify');
})->name('verify');

Route::get('/personal-area', function () {
    return view('personal-area');
})->name('personal-area');

Route::get('/training', function () {
    return view('training');
})->name('training');

Route::get('/news', function () {
    return view('news');
})->name('news');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/reset-password', function () {
    return view('reset-password');
})->name('reset-password');

Route::get('/about', function () {
    return view('about');
})->name('about');



Route::get('/cpp', function () {
    return view('cpp');
})->name('cpp');
Route::get('/cpp/{id}', function () {
    return view('cpp'); // Здесь Blade-шаблон, в котором подключается ваш Vue-бандл
})->where('id', '[0-9]+');




Route::get('/course/{id}', function ($id) {
    return view('admin.course', ['id' => $id]);
});
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/login', [AuthController::class, 'login']); // Обработка входа
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verify.email');


// Route::prefix('admin')
//     ->middleware(['auth', 'admin']) // доступ только авторизованным администраторам
//     ->group(function () {
//         // Главная админ-панели
//         Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
//         // Пример CRUD для управления курсами
//         Route::resource('courses', CourseController::class);
//     });

// Route::prefix('admin')->group(function () {
//     Route::get('/', function () {
//         // Здесь вернём Blade-шаблон,
//         // в котором будет подключён Vue-компонент
//         return view('admin.dashboard');
//     })->name('admin.dashboard');
// });
Route::get('/teacher', function () {
    return view('teacher');
})->middleware(['auth', 'role:2']);

Route::get('final-test/{course}', function ($course) {
    // $course здесь — это число из URL (/final-test/45)
    return view('final-test', ['courseId' => $course]);
});
Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth', 'role:3'] // только залогиненным пользователям с role=3
], function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    // можете добавить и другие маршруты админки здесь
});
    Route::prefix('admin')->group(function() {
        Route::get('/course/{id}', function ($id) {
            $course = Course::findOrFail($id);
            return view('admin.course', compact('course'));
        })->name('admin.course.show');
    });
    Route::prefix('admin')->name('admin.')->group(function () {
    
        // Маршруты для тем курса
        Route::get('/course/{course}/topics', [TopicController::class, 'index'])->name('topics.index');
        Route::post('/course/{course}/topics', [TopicController::class, 'store'])->name('topics.store');
        Route::get('/course/{course}/topics/json', [TopicController::class, 'getTopicsJson']);


        Route::get('/course/{course}/topics/create', [TopicController::class, 'create'])->name('topics.create');
        
        Route::get('/topics/{topic}/edit', [TopicController::class, 'edit'])->name('topics.edit');
        Route::put('/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
        Route::delete('/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
        
        // создание страницы для глав
        Route::get('/topic/{topic}/chapters/create', [ChapterController::class, 'create'])
         ->name('chapters.create');

         Route::get('/topic/{topic}', [TopicController::class, 'showes']); 
    
        // Маршруты для глав
        Route::get('/topic/{topic}/chapters', [ChapterController::class, 'index'])->name('chapters.index');
        Route::post('/topic/{topic}/chapters', [ChapterController::class, 'store'])->name('chapters.store');
        
        
        
        Route::get('/topics/{topic}/chapters/create', [ChapterController::class, 'create'])->name('chapters.create');
        Route::get('/chapters/{chapter}/edit', [ChapterController::class, 'edit'])->name('chapters.edit');
        Route::put('/chapters/{chapter}', [ChapterController::class, 'update'])->name('chapters.update');
        Route::delete('/chapters/{chapter}', [ChapterController::class, 'destroy'])->name('chapters.destroy');
    });


    Route::post('/resend-link', [AuthController::class, 'resendEmail'])
    ->name('resend.link');