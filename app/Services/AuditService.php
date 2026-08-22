<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Enregistrer un log d'audit.
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'role' => $user?->role,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * Raccourci pour une création.
     */
    public static function created(string $module, string $description, array $newValues, ?Request $request = null): AuditLog
    {
        return self::log("{$module}.create", $module, $description, null, $newValues, $request);
    }

    /**
     * Raccourci pour une modification.
     */
    public static function updated(string $module, string $description, array $oldValues, array $newValues, ?Request $request = null): AuditLog
    {
        return self::log("{$module}.update", $module, $description, $oldValues, $newValues, $request);
    }

    /**
     * Raccourci pour une suppression.
     */
    public static function deleted(string $module, string $description, array $oldValues, ?Request $request = null): AuditLog
    {
        return self::log("{$module}.delete", $module, $description, $oldValues, null, $request);
    }

    /**
     * Récupérer les logs filtrés.
     */
    public static function query(?string $module = null, ?string $action = null, int $limit = 100): \Illuminate\Database\Eloquent\Builder
    {
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if ($module) {
            $query->where('module', $module);
        }
        if ($action) {
            $query->where('action', $action);
        }

        return $query->limit($limit);
    }
}
