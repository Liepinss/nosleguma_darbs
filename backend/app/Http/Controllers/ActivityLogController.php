<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $rows = ActivityLog::query()
            ->with(['user:id,name,email'])
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        return response()->json($rows->map(fn (ActivityLog $l) => [
            'id' => $l->id,
            'action' => $l->action,
            'meta' => $l->meta,
            'ip' => $l->ip_address,
            'userAgent' => $l->user_agent,
            'at' => $l->created_at?->toIso8601String(),
            'user' => $l->user ? [
                'id' => $l->user->id,
                'name' => $l->user->name,
                'email' => $l->user->email,
            ] : null,
        ]));
    }
}
