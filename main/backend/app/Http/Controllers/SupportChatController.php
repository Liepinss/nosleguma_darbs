<?php

namespace App\Http\Controllers;

use App\Models\SupportChatMessage;
use App\Support\ActivityLogger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupportChatController extends Controller
{
    public function myUnreadCount(Request $request)
    {
        $uid = $request->user()->id;
        $n = SupportChatMessage::query()
            ->where('user_id', $uid)
            ->where('is_from_admin', true)
            ->whereNull('read_by_user_at')
            ->count();

        return response()->json(['unread' => $n]);
    }

    public function myIndex(Request $request)
    {
        $uid = $request->user()->id;

        SupportChatMessage::query()
            ->where('user_id', $uid)
            ->where('is_from_admin', true)
            ->whereNull('read_by_user_at')
            ->update(['read_by_user_at' => now()]);

        $rows = SupportChatMessage::query()
            ->where('user_id', $uid)
            ->orderBy('id')
            ->get();

        return $rows->map(fn (SupportChatMessage $m) => $this->mapMessage($m));
    }

    public function myStore(Request $request)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $msg = SupportChatMessage::create([
            'user_id' => $request->user()->id,
            'is_from_admin' => false,
            'admin_user_id' => null,
            'body' => $data['body'],
        ]);

        ActivityLogger::log($request, $request->user(), 'support.user_message', [
            'message_id' => $msg->id,
            'preview' => Str::limit($data['body'], 160),
        ]);

        return response()->json($this->mapMessage($msg), 201);
    }

    public function adminThreads(Request $request)
    {
        $sub = SupportChatMessage::query()
            ->select('user_id', DB::raw('MAX(created_at) as last_at'))
            ->groupBy('user_id');

        $threads = User::query()
            ->joinSub($sub, 't', 't.user_id', '=', 'users.id')
            ->orderByDesc('t.last_at')
            ->select(['users.id', 'users.name', 'users.email', 't.last_at'])
            ->get()
            ->map(function ($row) {
                $uid = $row->id;
                $last = SupportChatMessage::query()
                    ->where('user_id', $uid)
                    ->orderByDesc('id')
                    ->first();

                $unread = SupportChatMessage::query()
                    ->where('user_id', $uid)
                    ->where('is_from_admin', false)
                    ->whereNull('read_by_admin_at')
                    ->count();

                return [
                    'userId' => $uid,
                    'name' => $row->name,
                    'email' => $row->email,
                    'lastAt' => $last?->created_at?->toIso8601String(),
                    'lastPreview' => $last ? Str::limit($last->body, 120) : '',
                    'unreadFromUser' => $unread,
                ];
            });

        return response()->json($threads);
    }

    public function adminShow(Request $request, int $user)
    {
        $target = User::query()->findOrFail($user);

        SupportChatMessage::query()
            ->where('user_id', $target->id)
            ->where('is_from_admin', false)
            ->whereNull('read_by_admin_at')
            ->update(['read_by_admin_at' => now()]);

        $rows = SupportChatMessage::query()
            ->where('user_id', $target->id)
            ->orderBy('id')
            ->get();

        return response()->json([
            'user' => [
                'id' => $target->id,
                'name' => $target->name,
                'email' => $target->email,
            ],
            'messages' => $rows->map(fn (SupportChatMessage $m) => $this->mapMessage($m)),
        ]);
    }

    public function adminStore(Request $request, int $user)
    {
        $target = User::query()->findOrFail($user);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $admin = $request->user();

        $msg = SupportChatMessage::create([
            'user_id' => $target->id,
            'is_from_admin' => true,
            'admin_user_id' => $admin->id,
            'body' => $data['body'],
        ]);

        ActivityLogger::log($request, $admin, 'support.admin_reply', [
            'message_id' => $msg->id,
            'to_user_id' => $target->id,
            'to_email' => $target->email,
            'preview' => Str::limit($data['body'], 160),
        ]);

        return response()->json($this->mapMessage($msg), 201);
    }

    private function mapMessage(SupportChatMessage $m): array
    {
        return [
            'id' => $m->id,
            'userId' => $m->user_id,
            'isFromAdmin' => $m->is_from_admin,
            'body' => $m->body,
            'createdAt' => $m->created_at?->toIso8601String(),
        ];
    }
}
