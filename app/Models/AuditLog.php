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

    /**
     * Étape 1.1 — personne ne peut réécrire ou vider le journal :
     * Eloquent refuse update/delete sur l'entité (les entrées sont scellées).
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (AuditLog $log) {
            $log->previous_hash = static::latest()->value('hash');
            $log->hash = hash('sha256', (
                ($log->previous_hash ?? '')
                . '|' . ($log->user_id ?? '0')
                . '|' . $log->action
                . '|' . $log->module
                . '|' . $log->description
                . '|' . json_encode($log->new_values ?? [])
            ));
        });
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('id');
    }

    /**
     * Le journal est en lecture seule : Eloquent refuse toute réécriture
     * ou suppression — les lignes restent ineffaçables côté application.
     */
    protected static function booted(): void
    {
        static::updating(fn() => false);
        static::deleting(fn() => false);
    }

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
