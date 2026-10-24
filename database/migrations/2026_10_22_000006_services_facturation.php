<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étape 2 — Facturation des services (même flux que les commandes).
 *
 * - services_fournis reçoit les colonnes du flux acompte / solde :
 *   montant_paye, statut_paiement, numero_facture (SVC-...), numero_whatsapp,
 *   client_nom, prix_verrouille.
 * - factures et whatsapp_envois acceptent un service comme titulaire
 *   (commande_id devient nullable, service_id ajouté).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services_fournis', function (Blueprint $table) {
            $table->decimal('montant_paye', 10, 2)->default(0)->after('montant');
            $table->string('statut_paiement', 20)->default('a_payer')->after('montant_paye');
            $table->string('numero_facture', 30)->nullable()->after('statut_paiement');
            $table->string('numero_whatsapp', 30)->nullable()->after('numero_facture');
            $table->string('client_nom', 100)->nullable()->after('numero_whatsapp');
            $table->boolean('prix_verrouille')->default(false)->after('client_nom');

            $table->unique('numero_facture');
            $table->index('numero_whatsapp');
            $table->index(['superviseur_id', 'statut_paiement']);
        });

        Schema::table('factures', function (Blueprint $table) {
            // Une facture est désormais portée par une commande OU un service
            $table->dropConstrainedForeignId('commande_id');
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services_fournis')->nullOnDelete();

            $table->index(['service_id', 'phase']);
        });

        Schema::table('whatsapp_envois', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('commande_id')
                  ->constrained('services_fournis')->nullOnDelete();

            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_envois', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropIndex(['service_id']);
            $table->dropColumn('service_id');
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->dropIndex(['service_id', 'phase']);
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');

            // commande_id redevient NOT NULL uniquement si aucune facture service n'existe
            if (\Illuminate\Support\Facades\DB::table('factures')->whereNull('commande_id')->doesntExist()) {
                $table->foreignId('commande_id')->change();
            }
        });

        Schema::table('services_fournis', function (Blueprint $table) {
            $table->dropUnique(['numero_facture']);
            $table->dropIndex('services_fournis_numero_whatsapp_index');
            $table->dropIndex('services_fournis_superviseur_id_statut_paiement_index');
            $table->dropColumn([
                'montant_paye', 'statut_paiement', 'numero_facture',
                'numero_whatsapp', 'client_nom', 'prix_verrouille',
            ]);
        });
    }
};
