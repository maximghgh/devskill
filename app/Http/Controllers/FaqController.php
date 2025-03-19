<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function store(Request $request)
    {
        // Валидация данных
        $data = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string'
        ]);

        // Создание записи с установкой дефолтного значения, если категория не передана
        $faq = Faq::create([
            'question' => $data['question'],
            'answer' => $data['answer']
        ]);

        return response()->json($faq, 201);
    }
    public function index()
    {
        $faqs = Faq::all();
        return response()->json($faqs);
    }
}

