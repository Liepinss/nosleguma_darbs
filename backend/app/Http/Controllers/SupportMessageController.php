<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class SupportMessageController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string'],
            'selected_animals' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        $message = ContactMessage::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'message' => $data['message'],
            'selected_animals' => $data['selected_animals'] ?? null,
            'source' => 'support',
            'status' => 'pending',
            'is_read' => false,
            'sent_at' => now(),
        ]);

        ActivityLogger::log($request, $user, 'support.ticket_submitted', [
            'message_id' => $message->id,
            'email' => $user->email,
        ]);

        return response()->json([
            'id' => $message->id,
        ], 201);
    }
}
