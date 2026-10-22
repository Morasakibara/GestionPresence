<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->string('numero_whatsapp', 30)->nullable()->after('details');
            $table->string('client_nom')->nullable()->after('numero_whatsapp');
            $table->string('numero_facture', 30)->nullable()->unique()->after('client_nom');
            $table->boolean('prix_verrouille')->default(false)->after('numero_facture');

            $table->index('numero_whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['numero_whatsapp', 'client_nom', 'numero_facture', 'prix_verrouille']);
        });
    }
};
