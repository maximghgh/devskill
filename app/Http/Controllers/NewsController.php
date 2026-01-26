<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Comment;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Http\Resources\NewsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    // Получение списка новостей
    public function index()
    {
        $news = News::orderBy('created_at', 'desc')->get();
        return response()->json(NewsResource::collection($news));
    }
    // Получение детальной информации о новости с комментариями
    public function show($id)
    {
        $news = News::findOrFail($id);

        $rootComments = Comment::where('news_id', $news->id)
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                // Сортируем и подкомментарии
                $q->orderBy('created_at', 'asc');
            }])
            ->orderBy('created_at', 'asc') // Сортируем родительские комментарии
            ->get();

        $news->setAttribute('comments', $rootComments);

        return response()->json(new NewsResource($news));
    }
    // Создание новой новости
    public function store(StoreNewsRequest $request)
    {
        $data = $request->validated();

        // Обработка загрузки изображения
        if ($request->hasFile('image')) {
            // Сохраняем файл в директории news_images в диске public и получаем путь
            $data['image'] = $request->file('image')->store('news_images', 'public');
        }

        // Если editor_data приходит в виде массива, преобразуем его в JSON
        if (isset($data['editor_data']) && is_array($data['editor_data'])) {
            $data['editor_data'] = json_encode($data['editor_data']);
        }

        $news = News::create($data);
        return response()->json(new NewsResource($news), 201);
    }
    // Обновление существующей новости
    public function update(UpdateNewsRequest $request, $id)
    {
        $news = News::findOrFail($id);

        $data = $request->validated();

        // загружаем новую картинку
        if ($request->hasFile('image')) {
            // удалить старую, если была
            if ($news->image) {
                Storage::disk('public')->delete($news->image);
            }
            $path        = $request->file('image')->store('news_images', 'public');
            $data['image'] = $path;               // сохраняем путь
        }

        // сериализуем editor_data
        if (isset($data['editor_data']) && is_array($data['editor_data'])) {
            $data['editor_data'] = json_encode($data['editor_data']);
        }

        $news->update($data);

        // !!! возвращаем в формате { news: {...} }, как ждёт фронт
        return response()->json([
            'news' => new NewsResource($news->fresh()),             // fresh() — сразу с новым image
        ]);
    }
   //Удаления новости
    public function destroy($id)
    {
        $news = News::findOrFail($id);

        // (Опционально) Удаляем файл изображения, если он существует
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }

        $news->delete();
        return response()->json(['message' => 'Новость удалена']);
    }
}
