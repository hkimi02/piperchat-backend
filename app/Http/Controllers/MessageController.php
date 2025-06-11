<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Chatroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request, Chatroom $chatroom)
    {
        // Ensure the user is part of the organisation that owns the chatroom
        if ($request->user()->organisation_id !== $chatroom->organisation_id) {
            abort(403);
        }

        return $chatroom->messages()->with('user')->latest()->get();
    }

    public function store(Request $request, Chatroom $chatroom)
    {
        // Ensure the user is part of the organisation that owns the chatroom
        if ($request->user()->organisation_id !== $chatroom->organisation_id) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $user = $request->user();

        $message = $chatroom->messages()->create([
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        // Broadcast the message to the chatroom channel
        broadcast(new MessageSent($message->load('user')));

        return $message->load('user');
    }
}
