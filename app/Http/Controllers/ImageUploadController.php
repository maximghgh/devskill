<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function store(Request $request)
    {
        // 1. Валидируем, что загружается файл изображения
        $request->validate([
            'image' => 'required|image'
        ]);

        // 2. Сохраняем файл в папку public/uploads (storage/app/public/uploads)
        //    Не забудьте выполнить команду php artisan storage:link для доступа из public
        $path = $request->file('image')->store('public/uploads');

        // 3. Получаем публичную ссылку
        //    Если вы используете disk, то можете указать его: Storage::disk('public')->url(...)
        $url = Storage::url($path);

        // 4. Возвращаем ответ в формате, который ожидает Editor.js Image Tool
        return response()->json([
            'success' => 1,
            'file' => [
                'url' => $url
            ]
        ]);
    }
}
