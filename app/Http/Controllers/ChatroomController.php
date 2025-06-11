<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatroomController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->organisation->chatrooms;
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $chatroom = $request->user()->organisation->chatrooms()->create([
            'name' => $validated['name'],
        ]);

        return response()->json($chatroom, 201);
    }
}
