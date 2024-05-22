<?php

namespace App\Http\Controllers;

use App\Models\ProjectMedia;
use Storage;

class FileController extends Controller
{
    //
    public function download($file)
    {
        // Проверяем наличие записи в базе данных
        $fileRecord = ProjectMedia::where('file_name', $file)->first();

        if (!$fileRecord) {
            return response()->json(['message' => 'File not found in database.'], 404);
        }

        // Проверяем наличие файла на диске
        $filePath = 'public/snapshots/downloadable/' . $file;
        if (!Storage::exists($filePath)) {
            return response()->json(['message' => 'File not found on disk.'], 404);
        }

        // Возвращаем файл для скачивания
        return Storage::download($filePath);
    }
}
