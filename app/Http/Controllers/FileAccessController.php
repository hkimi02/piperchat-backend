<?php

namespace App\Http\Controllers;

use App\Models\File;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileAccessController extends Controller
{
    /**
     * Retrieve a project file and stream it.
     *
     * @param string $projectId
     * @param string $filename
     * @return StreamedResponse|void
     */
    public function getProjectFile(string $projectId, string $filename)
    {
        // Construct the file path as stored by Laravel.
        $filePath = "public/projects/{$projectId}/files/{$filename}";

        // Find the file record in the database.
        $file = File::where('path', $filePath)->first();

        // If the file doesn't exist in the database or storage, abort.
        if (!$file || !Storage::exists($filePath)) {
            abort(404);
        }

        // Stream the file as a response.
        return Storage::response($filePath);
    }
}
