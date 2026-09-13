<?php

namespace App\Listeners;

use App\Events\ModelActivityLogged;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogModelActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ModelActivityLogged $event): void
    {
        $userId = $event->userId;
        
        // Cek apakah user masih exist — jika dihapus, set null biar ga kena FK constraint
        if ($userId !== null && !User::where('id', $userId)->exists()) {
            $userId = null;
        }

        ActivityLog::create([
            'user_id' => $userId,
            'model_type' => $event->modelType,
            'model_id' => $event->modelId,
            'action' => $event->action,
            'changes' => json_encode($event->changes),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
