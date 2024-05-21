<?php

namespace App\Http\Controllers;

class FileController extends Controller
{
    //
    public function download($file)
    {
        $filePath = storage_path('app/' . $file);

        // Check if the file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        // Download the file
        $response = Response::download($filePath);

        // Redirect back to the previous page
        return $response->withHeaders([
            'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"'
        ])->send();

        return Redirect::back();
    }
}
