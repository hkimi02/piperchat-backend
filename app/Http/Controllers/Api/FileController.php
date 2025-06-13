<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Chatroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    use AuthorizesRequests;
    public function store(Request $request, Chatroom $chatroom)
    {
        $this->authorize('view', $chatroom);
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'message_id' => 'nullable|exists:messages,id'
        ]);

        $file = $request->file('file');
        $path = $file->store('public/chatrooms/' . $chatroom->id . '/files');

        $newFile = File::create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'user_id' => Auth::id(),
            'chatroom_id' => $chatroom->id,
            'project_id' => $chatroom->project_id,
            'message_id' => $request->message_id,
        ]);

        return response()->json($newFile, 201);
    }

    public function show(File $file)
    {
        $this->authorize('view', $file);
        return Storage::download($file->path, $file->name);
    }

    public function destroy(File $file)
    {
        $this->authorize('delete', $file);

        Storage::delete($file->path);
        $file->delete();

        return response()->json(null, 204);
    }
}
