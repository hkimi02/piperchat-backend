<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BroadcastingController extends Controller
{
    public function authentificate(Request $request): \Illuminate\Http\JsonResponse
    {
        // Get token from request
        $token = $request->bearerToken();
        if (!$token || !auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $channelName = $request->input('channel_name');

        return response()->json([
            'auth' => $request->input('socket_id') . ':' .
                hash_hmac('sha256', $request->input('socket_id') . ':' . $channelName,
                    env('pusher_app_id'))
        ]);
    }
}
