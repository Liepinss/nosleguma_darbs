<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $rows = User::query()->orderBy('id')->get();

        return response()->json($rows->map(fn (User $u) => $this->userArray($u)));
    }

    public function update(Request $request, int $id)
    {
        $target = User::findOrFail($id);

        if ($target->id === $request->user()->id) {
            return response()->json(['message' => 'Nevar mainīt pašu sevi.'], 422);
        }

        if ($this->isPanelAdminAccount($target)) {
            return response()->json(['message' => 'Šo sistēmas kontu nedrīkst mainīt.'], 422);
        }

        if ($target->is_owner) {
            return response()->json(['message' => 'Īpašnieka kontu nedrīkst bloķēt vai mainīt admin tiesības.'], 422);
        }

        $data = $request->validate([
            'is_blocked' => ['sometimes', 'boolean'],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        $wasAdmin = (bool) $target->is_admin;

        if (array_key_exists('is_blocked', $data)) {
            $target->is_blocked = $data['is_blocked'];
            if ($data['is_blocked']) {
                $target->last_logout_at = now();
            }
        }

        if (array_key_exists('is_admin', $data)) {
            $target->is_admin = $data['is_admin'];
        }

        $target->save();

        if (array_key_exists('is_admin', $data) && $data['is_admin'] && ! $wasAdmin) {
            ContactMessage::query()
                ->where('email', $target->email)
                ->where('source', 'admin_role_grant')
                ->delete();

            ContactMessage::create([
                'user_id' => $target->id,
                'name' => 'Administrators',
                'email' => $target->email,
                'message' => 'Jums piešķīra administratora lomu. admin parole: '.config('admin.panel_password'),
                'selected_animals' => '',
                'source' => 'admin_role_grant',
                'status' => 'approved',
                'is_read' => false,
                'sent_at' => now(),
                'moderated_at' => now(),
                'approved_at' => now(),
            ]);
        }

        return response()->json($this->userArray($target->fresh()));
    }

    private function isPanelAdminAccount(User $user): bool
    {
        return $user->email === 'panel-admin@laimigas-kepas.local';
    }

    /**
     * @return array<string, mixed>
     */
    private function userArray(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'is_blocked' => (bool) $u->is_blocked,
            'is_admin' => (bool) $u->is_admin,
            'is_owner' => (bool) $u->is_owner,
            'last_login_at' => $u->last_login_at?->toIso8601String(),
            'last_logout_at' => $u->last_logout_at?->toIso8601String(),
        ];
    }
}
