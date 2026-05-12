<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Support\ActivityLogger;
use App\Models\User;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        $email = $request->user()->email;

        $rows = ContactMessage::query()
            ->where('email', $email)
            ->whereIn('status', ['approved', 'declined'])
            ->orderByDesc('id')
            ->get();

        return response()->json($rows->map(fn (ContactMessage $m) => $this->messageArray($m)));
    }

    public function markRead(Request $request)
    {
        $email = $request->user()->email;

        ContactMessage::query()
            ->where('email', $email)
            ->where('status', 'approved')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, int $id)
    {
        $email = $request->user()->email;

        $deleted = ContactMessage::query()
            ->whereKey($id)
            ->where('email', $email)
            ->delete();

        if ($deleted) {
            ActivityLogger::log($request, $request->user(), 'notification.deleted', [
                'notification_id' => $id,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function declineAdminRole(Request $request)
    {
        $data = $request->validate([
            'message_id' => ['required', 'integer'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $message = ContactMessage::query()
            ->whereKey($data['message_id'])
            ->where('email', $user->email)
            ->where('source', 'admin_role_grant')
            ->firstOrFail();

        if ($user->is_owner) {
            return response()->json(['message' => 'Īpašniekam nevar noņemt admin tiesības šādā veidā.'], 422);
        }

        $message->delete();
        $user->forceFill(['is_admin' => false])->save();

        ActivityLogger::log($request, $user, 'user.declined_admin_role', [
            'message_id' => $data['message_id'],
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function messageArray(ContactMessage $m): array
    {
        return [
            'id' => $m->id,
            'name' => $m->name,
            'email' => $m->email,
            'message' => $m->message,
            'selected_animals' => $m->selected_animals,
            'source' => $m->source,
            'status' => $m->status,
            'is_read' => (bool) $m->is_read,
            'sent_at' => $m->sent_at?->toIso8601String(),
            'moderated_at' => $m->moderated_at?->toIso8601String(),
            'approved_at' => $m->approved_at?->toIso8601String(),
        ];
    }
}
