<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_envois', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->nullable()->constrained('factures')->nullOnDelete();
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->nullOnDelete();
            $table->unsignedBigInteger('superviseur_id')->nullable();
            $table->string('telephone', 30);            // numéro du destinataire
            $table->string('canal', 20);                // driver : 'meta' | 'log'
            $table->string('statut', 20);               // 'en_attente' | 'envoye' | 'echec'
            $table->string('message_id', 64)->nullable();
            $table->string('erreur', 500)->nullable();
            $table->unsignedSmallInteger('tentative')->default(1);
            $table->timestamps();

            $table->index(['statut', 'created_at']);
            $table->index('commande_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_envois');
    }
};
