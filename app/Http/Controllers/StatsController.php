<?php
namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\Direction;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        // всего курсов
        $coursesCount = Course::count();
        // экспертов: пользователи с role = 2 или 3
        $expertsCount = User::whereIn('role', [2, 3])->count();
        // направлений
        $directionsCount = Direction::count();

        return response()->json([
            'courses'    => $coursesCount,
            'experts'    => $expertsCount,
            'directions' => $directionsCount,
        ]);
    }
}
