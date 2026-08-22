<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->decimal('montant_paye', 10, 2)->default(0)->after('montant');
            $table->string('statut_paiement')->default('paye')->after('montant_paye');
            // statut_paiement : 'paye' | 'partiel' | 'a_payer'
            // paye     = montant_paye == montant (comptabilisé en caisse)
            // partiel  = montant_paye > 0 && < montant (restant = negatif en caisse)
            // a_payer  = montant_paye == 0 (sera comptabilisé à la livraison)
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['montant_paye', 'statut_paiement']);
        });
    }
};
