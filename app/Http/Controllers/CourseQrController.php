<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseQrController extends Controller
{
    public function show(Course $course)
    {
        $qr = $course->qrCodes()->first();

        if (!$qr) {
            return response()->json([
                'qr' => null,
            ]);
        }

        return response()->json([
            'qr' => [
                'id' => $qr->id,
                'qr_image' => $qr->qr_image,
                'link' => $qr->link,
            ],
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'qr' => 'nullable|file|image|max:5120',
            'link' => 'required|url|max:2048',
        ]);

        $qr = $course->qrCodes()->first();

        if (!$request->hasFile('qr') && !$qr) {
            return response()->json([
                'message' => 'Attach a QR code.',
            ], 422);
        }

        $newPath = null;
        if ($request->hasFile('qr')) {
            $path = $request->file('qr')->store('public/qr');
            $newPath = str_replace('public/', 'storage/', $path);
        }

        if ($qr) {
            if ($newPath) {
                if ($qr->qr_image) {
                    Storage::delete(str_replace('storage/', 'public/', $qr->qr_image));
                }
                $qr->qr_image = $newPath;
            }
            $qr->link = $validated['link'];
            $qr->save();
        } else {
            $qr = QrCode::create([
                'qr_image' => $newPath,
                'link' => $validated['link'],
            ]);
            $course->qrCodes()->attach($qr->id);
        }

        return response()->json([
            'qr' => [
                'id' => $qr->id,
                'qr_image' => $qr->qr_image,
                'link' => $qr->link,
            ],
        ]);
    }

    public function destroy(Course $course)
    {
        $qr = $course->qrCodes()->first();

        if (!$qr) {
            return response()->json([
                'message' => 'QR code not found.',
            ], 404);
        }

        if ($qr->qr_image) {
            Storage::delete(str_replace('storage/', 'public/', $qr->qr_image));
        }

        $course->qrCodes()->detach($qr->id);
        $qr->delete();

        return response()->json([
            'message' => 'QR code deleted.',
        ]);
    }
}
