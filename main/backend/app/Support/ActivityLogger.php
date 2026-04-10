<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function log(?Request $request, ?User $user, string $action, array $meta = []): void
    {
        $req = $request ?? request();

        ActivityLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'meta' => $meta === [] ? null : $meta,
            'ip_address' => $req?->ip(),
            'user_agent' => self::truncateUa($req?->userAgent()),
        ]);
    }

    private static function truncateUa(?string $ua): ?string
    {
        if ($ua === null || $ua === '') {
            return null;
        }

        return mb_substr($ua, 0, 500);
    }
}
