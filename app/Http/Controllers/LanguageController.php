<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $language = Language::create([
            'name' => $validated['name']
        ]);

        return response()->json([
            'message' => 'Категория успешно создана!',
            'language' => $language
        ], 201);
    }
    public function index()
    {
        // Получаем все языки из базы
        $languages = Language::all(['id', 'name']);
        return response()->json($languages);
    }
}

