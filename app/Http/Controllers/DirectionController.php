<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use Illuminate\Http\Request;

class DirectionController extends Controller
{
    public function store(Request $request)
    {
        // Валидация входящих данных
        $validatedData = $request->validate([
           'name' => 'required|string|max:255',
           'description' => 'nullable|string',
        ]);

        // Создание нового направления
        $direction = Direction::create($validatedData);

        return response()->json([
           'message' => 'Направление успешно добавлено',
           'direction' => $direction
        ], 201);
    }
    public function index()
    {
        // Получаем все направления из таблицы directions
        $directions = Direction::all();
        return response()->json($directions);
    }
}
