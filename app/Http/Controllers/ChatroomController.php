<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatroomController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $organisation = $user->organisation;

        if (!$organisation) {
            return response()->json([]);
        }

        // Get organisation and project chatrooms
        $organisationChatrooms = $organisation->chatrooms()
            ->whereIn('type', ['organisation', 'project'])
            ->get();

        // Get private chatrooms the user is a member of
        $privateChatrooms = $user->chatrooms()
            ->where('type', 'private')
            ->with('users')
            ->get();

        $allChatrooms = $organisationChatrooms->merge($privateChatrooms)->unique('id');

        return response()->json($allChatrooms);
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
        ]);
        $organisation_id = Auth::user()->role === 'ADMIN' ? Organisation::where('admin_id',Auth::id()) : Auth::user()->organisation_id;

        $chatroom = $request->user()->organisation->chatrooms()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'project_id' => $validated['type'] === 'project' ? $validated['project_id'] : null,
            'organisation_id' => $organisation_id,
        ]);

        return response()->json($chatroom, 201);
    }

    public function findOrCreatePrivateChatroom(Request $request)
    {
        $validated = $request->validate([
            'userId' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        $otherUserId = $validated['userId'];

        if ($user->id == $otherUserId) {
            return response()->json(['message' => 'You cannot create a chat with yourself.'], 422);
        }

        $chatroom = $user->chatrooms()
            ->where('type', 'private')
            ->whereHas('users', function ($query) use ($otherUserId) {
                $query->where('users.id', $otherUserId);
            })
            ->where(function ($query) {
                $query->has('users', '=', 2);
            })
            ->with('users')
            ->first();

        if ($chatroom) {
            $chatroom->load('users');
            return response()->json($chatroom);
        }

        $otherUser = User::find($otherUserId);

        $newChatroom = $user->organisation->chatrooms()->create([
            'name' => 'Chat with ' . $otherUser->full_name,
            'type' => 'private',
            'organisation_id' => $user->organisation_id,
        ]);

        $newChatroom->users()->attach([$user->id, $otherUserId]);
        $newChatroom->load('users');

        return response()->json($newChatroom, 201);
    }
}
