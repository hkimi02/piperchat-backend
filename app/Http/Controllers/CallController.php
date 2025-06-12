<?php

namespace App\Http\Controllers;

use App\Models\Chatroom;
use Illuminate\Http\Request;
use App\Events\SignalingEvent;

class CallController extends Controller
{
    /**
     * Relays a signaling message (offer, answer, ICE candidate) to other users in the call.
     */
    public function signal(Request $request, Chatroom $chatroom)
    {
        $user = $request->user();
        $payload = $request->all();
        
        // Add the sender's ID to the payload so clients know who it's from
        $payload['from'] = $user->id;

        // Broadcast to others in the same chatroom, excluding the sender
        broadcast(new SignalingEvent($chatroom, $payload))->toOthers();

        return response()->json(['message' => 'Signal relayed.']);
    }
}
