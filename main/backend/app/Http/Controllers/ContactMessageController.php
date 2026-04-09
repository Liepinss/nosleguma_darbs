<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'message' => ['required', 'string'],
            'selected_animals' => ['nullable', 'string', 'max:2000'],
        ]);

        $message = ContactMessage::create([
            'user_id' => null,
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'selected_animals' => $data['selected_animals'] ?? null,
            'source' => 'contact',
            'status' => 'pending',
            'is_read' => false,
            'sent_at' => now(),
        ]);

        return response()->json($this->messageArray($message), 201);
    }

    public function adminIndex()
    {
        $rows = ContactMessage::query()->orderByDesc('id')->get();

        return response()->json($rows->map(fn (ContactMessage $m) => $this->messageArray($m)));
    }

    public function adminUpdate(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,declined'],
        ]);

        $message = ContactMessage::findOrFail($id);
        $now = now();

        $message->status = $data['status'];
        $message->moderated_at = $now;
        if ($data['status'] === 'approved') {
            $message->approved_at = $now;
        }
        $message->is_read = false;
        $message->save();

        return response()->json($this->messageArray($message));
    }

    public function adminDestroy(int $id)
    {
        ContactMessage::destroy($id);

        return response()->json(['ok' => true]);
    }

    /** Admin atceļ „admin lomas” paziņojumu un noņem lomu lietotājam. */
    public function adminCancelRoleGrant(Request $request)
    {
        $data = $request->validate([
            'message_id' => ['required', 'integer'],
        ]);

        $message = ContactMessage::query()
            ->whereKey($data['message_id'])
            ->where('source', 'admin_role_grant')
            ->firstOrFail();

        $email = $message->email;
        $targetUser = User::query()->where('email', $email)->first();

        if ($targetUser && $targetUser->is_owner) {
            return response()->json(['message' => 'Īpašnieka admin lomu nedrīkst noņemt.'], 422);
        }

        $message->delete();

        if ($targetUser) {
            $targetUser->forceFill(['is_admin' => false])->save();
        }

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function messageArray(ContactMessage $m): array
    {
        return [
            'id' => $m->id,
            'user_id' => $m->user_id,
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
