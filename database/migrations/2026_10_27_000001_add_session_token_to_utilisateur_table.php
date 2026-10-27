<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Session unique : empreinte SHA-256 du jeton de session actuellement actif.
 * Émise à chaque connexion (email/mot de passe ou récupération PIN). Une session
 * dont le jeton ne correspond plus à cette colonne est invalidée → un seul
 * appareil connecté à la fois.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->string('session_token', 64)->nullable()->after('pin_required');
        });
    }

    public function down(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->dropColumn('session_token');
        });
    }
};
