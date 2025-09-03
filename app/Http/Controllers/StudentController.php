<?php

namespace App\Http\Controllers;

use App\Models\User;

class StudentController extends Controller
{
    public function listByCourse($courseId)
    {
        $students = User::whereHas('purchases', function($q) use ($courseId) {
            $q->where('course_id', $courseId);
        })->get();

        return response()->json($students);
    }
}
?>