<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Добавление нового комментария или ответа

     public function index($newsId)
    {
        $comments = Comment::with('user:id,name,photo')
            ->where('news_id', $newsId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function (Comment $c) {
                return [
                    'id'           => $c->id,
                    'body'         => $c->body,
                    'created_at'   => $c->created_at->toDateTimeString(),
                    'likes'        => $c->likes,
                    'dislikes'     => $c->dislikes,
                    'parent_id'    => $c->parent_id,
                    // вложенные ответы (если нужны — можно рекурсивно)
                    'children'     => $c->children->map(function($child) {
                        return [
                            'id'           => $child->id,
                            'body'         => $child->body,
                            'created_at'   => $child->created_at->toDateTimeString(),
                            'likes'        => $child->likes,
                            'dislikes'     => $child->dislikes,
                            'parent_id'    => $child->parent_id,
                            'user_name'    => $child->user_name,
                            'user_avatar'  => $child->user
                                                 ? "/storage/{$child->user->photo}"
                                                 : null,
                        ];
                    }),
                    // автор
                    'user_name'   => $c->user_name ?: ($c->user->name ?? 'Аноним'),
                    'user_avatar' => $c->user
                                        && $c->user->photo
                                      ? "/storage/{$c->user->photo}"
                                      : null,
                ];
            });

        return response()->json($comments);
    }
    public function store(Request $request)
    {
        // Валидируем входящие данные
        $data = $request->validate([
            'news_id'   => 'required|exists:news,id',
            'body'      => 'required|string',
            'parent_id' => 'nullable|exists:comments,id', // если передаётся, это ответ на другой комментарий
            'user_id'   => 'nullable|exists:users,id',      // если пользователь авторизован
            'user_name' => 'nullable|string',
        ]);

        // Если поле user_name не передано, устанавливаем "Аноним"
        if (empty($data['user_name'])) {
            $data['user_name'] = 'Аноним';
        }

        // Создаём комментарий
        $comment = Comment::create($data);
        return response()->json($comment, 201);
    }

    // Обновление комментария (если требуется)
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        $data = $request->validate([
            'body' => 'required|string',
        ]);

        $comment->update($data);
        return response()->json($comment);
    }

    // Удаление комментария
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return response()->json(['message' => 'Комментарий удален']);
    }

    public function like($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->likes = $comment->likes + 1;
        $comment->save();
        return response()->json($comment);
    }

    public function dislike($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->dislikes = $comment->dislikes + 1;
        $comment->save();
        return response()->json($comment);
    }

    public function unlike($id)
    {
        $comment = Comment::findOrFail($id);
        if ($comment->likes > 0) {
            $comment->likes -= 1;
            $comment->save();
        }
        return response()->json($comment);
    }   

    // Для отмены дизлайка
    public function undislike($id)
    {
        $comment = Comment::findOrFail($id);
        if ($comment->dislikes > 0) {
            $comment->dislikes -= 1;
            $comment->save();
        }
        return response()->json($comment);
    }
}
