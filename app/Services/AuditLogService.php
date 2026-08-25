<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an audit event to the database.
     *
     * @param string $action e.g. TICKET_CREATED, STATUS_UPDATED, TICKET_RATED
     * @param string $entityType e.g. Ticket, User, Department, Rating
     * @param int|null $entityId
     * @param array|object|null $oldValues
     * @param array|object|null $newValues
     * @param int|null $userId
     * @param string|null $userName
     */
    public static function log(
        string $action,
        string $entityType,
        $entityId = null,
        $oldValues = null,
        $newValues = null,
        $userId = null,
        $userName = null
    ) {
        try {
            $user = Auth::user();
            $effectiveUserId = $userId ?? ($user ? $user->id : null);
            $effectiveUserName = $userName ?? ($user ? $user->name : 'System');

            DB::table('audit_logs')->insert([
                'user_id' => $effectiveUserId,
                'user_name' => $effectiveUserName,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently log or ignore to not break main business transactions
            \Log::error('Failed to write audit log: ' . $e->getMessage());
        }
    }
}
