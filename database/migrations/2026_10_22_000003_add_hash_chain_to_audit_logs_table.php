<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étape 1.1 — Verrou du journal d'audit : chaîne de hachage.
 *
 * Chaque entrée=d faut signé avec le hash de la précédente (sha256) :
 * supprimer ou modifier un log viderait la chaîne et rendait la fraude
 * immédiatement visible (la vérification d'intégrité échoue).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('previous_hash', 64)->nullable()->after('user_agent');
            $table->string('hash', 64)->nullable()->after('previous_hash');
            $table->index('hash');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['hash']);
            $table->dropColumn(['previous_hash', 'hash']);
        });
    }
};
