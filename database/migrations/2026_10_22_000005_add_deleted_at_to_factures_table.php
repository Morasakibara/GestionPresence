<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Correction : le modèle Facture utilise SoftDeletes mais la colonne
 * deleted_at n'avait pas été créée — toute requête Facture::where()
 * échouait avec « Unknown column 'factures.deleted_at' ».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
