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
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $direction = Direction::findOrFail($id);
        $direction->name = $validated['name'];
        $direction->save();

        return response()->json([
            'success' => true,
            'direction' => $direction,
        ]);
    }

    public function destroy($id)
    {
        $direction = Direction::findOrFail($id);
        $direction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Направление успешно удалено',
        ]);
    }
}
