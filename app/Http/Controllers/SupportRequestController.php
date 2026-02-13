<?php

namespace App\Http\Controllers;

use App\Models\SupportRequest;
use Illuminate\Http\Request;

class SupportRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportRequest::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'user_name' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $supportRequest = SupportRequest::create([
            'user_id' => $validated['user_id'] ?? null,
            'user_name' => $validated['user_name'],
            'message' => $validated['message'],
            'status' => 'открыта',
        ]);

        return response()->json([
            'success' => true,
            'support_request' => $supportRequest,
            'message' => 'Обращение успешно отправлено',
        ], 201);
    }

    public function updateStatus(Request $request, SupportRequest $supportRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:открыта,выполнена',
        ]);

        $supportRequest->status = $validated['status'];
        $supportRequest->completed_at =
            $validated['status'] === 'выполнена' ? now() : null;
        $supportRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Статус обращения обновлён',
        ]);
    }
}
