<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id', 'role', 'action', 'module', 'description',
        'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'user_id');
    }

    /**
     * Couleur badge selon l'action.
     */
    public function badgeColor(): string
    {
        return match (true) {
            str_contains($this->action, 'create') || str_contains($this->action, 'store') => 'bg-green-100 text-green-800',
            str_contains($this->action, 'update') => 'bg-blue-100 text-blue-800',
            str_contains($this->action, 'delete') || str_contains($this->action, 'destroy') => 'bg-red-100 text-red-800',
            str_contains($this->action, 'login') => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function icon(): string
    {
        return match (true) {
            str_contains($this->action, 'create') || str_contains($this->action, 'store') => '➕',
            str_contains($this->action, 'update') => '✏️',
            str_contains($this->action, 'delete') || str_contains($this->action, 'destroy') => '🗑️',
            str_contains($this->action, 'retrait') => '💰',
            str_contains($this->action, 'evaluation') => '⭐',
            default => '📋',
        };
    }
}
