<?php

namespace App\Http\Controllers;

use App\Models\File;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileAccessController extends Controller
{
    /**
     * Retrieve and stream a file.
     *
     * @param \App\Models\File $file
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function show(File $file): StreamedResponse
    {
        // The 'signed' middleware has already verified the request.
        // We can safely stream the file.
        return Storage::response($file->path);
    }
}
