<?php

namespace App\Http\Controllers;
use App\Models\Course;

use Illuminate\Http\Request;
use PDF;  

class CertificateController extends Controller
{
    public function generate(Request $request, Course $course)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|integer',
                'name'    => 'required|string|max:255',
            ]);

            // достаём курс из БД
            $logo = str_replace('\\','/', public_path('img/logo.svg'));
            $bg = str_replace('\\', '/', public_path('img/certificat.png'));

            $pdf = PDF::loadView('certificate', [
                'name'   => $data['name'],
                'logo'  => $logo,
                'course' => $course->course_name, 
                'bg'     => $bg,   // ← теперь переменная есть
            ])->setPaper('a4', 'landscape');

            return $pdf->download("certificate_{$data['user_id']}.pdf");

        } catch (\Throwable $e) {
            \Log::error($e);
            return response()->json([
                'message' => 'Ошибка генерации сертификата',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
