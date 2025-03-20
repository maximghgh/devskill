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
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $language = Language::findOrFail($id);
        $language->name = $validated['name'];
        $language->save();

        return response()->json([
            'success' => true,
            'language' => $language,
        ]);
    }
    public function destroy($id)
    {
        $language = Language::findOrFail($id);
        $language->delete();

        return response()->json([
            'success' => true,
            'message' => 'Язык успешно удалён'
        ]);
    }


}

