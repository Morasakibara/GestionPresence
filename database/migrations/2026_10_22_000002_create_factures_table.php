<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étapes 3.1 — Snapshots immuables des factures générées (Étape 3.1).
 *
 * Une facture est figée : les montants sont copiés au moment de la génération
 * et un hash SHA-256 du PDF est conservé pour garantir l'infalsificabilité.
 * Deux phases : 'acompte' (PDF de l'acompte) puis 'solde' (PDF final payé).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->string('numero', 30);                  // ex: FAC-2026-00001
            $table->string('phase', 20);                    // 'acompte' | 'solde'
            $table->string('type');
            $table->string('client_nom')->nullable();
            $table->string('client_whatsapp', 30);
            $table->decimal('montant_total', 10, 2);
            $table->decimal('montant_acompte', 10, 2);
            $table->decimal('solde', 10, 2);                // montant_total - montant_acompte
            $table->string('hash', 64);                     // sha256 du PDF généré
            $table->string('pdf_path');                     // chemin du PDF figé (storage.app)
            $table->timestamps();

            $table->unique(['numero', 'phase']);
            $table->index(['commande_id', 'phase']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
