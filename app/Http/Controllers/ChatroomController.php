<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatroomController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->organisation->chatrooms ?? [];
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:project,organisation',
            'project_id' => 'nullable|exists:projects,id',
            'organisation_id' => 'required|exists:organisations,id',
        ]);

        $chatroom = $request->user()->organisation->chatrooms()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'project_id' => $validated['type'] === 'project' ? $validated['project_id'] : null,
            'organisation_id' => $validated['organisation_id'],
        ]);

        return response()->json($chatroom, 201);
    }
}
