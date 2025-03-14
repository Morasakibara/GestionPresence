<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Exécute les migrations.
     */
    public function up(): void
    {
        Schema::create('rapport', function (Blueprint $table) {
            $table->id(); // Colonne id (auto-incrémentée par défaut)
            $table->unsignedBigInteger('Adm_id'); // Référence à Administrateur
            $table->unsignedBigInteger('Sup_id')->nullable(); // Référence à Superviseur (nullable)
            $table->string('periode', 254);
            $table->string('contenu', 254);
            $table->timestamps(); // Ajoute created_at et updated_at

            // Clés étrangères
            $table->foreign('Adm_id')->references('id')->on('administrateur')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('Sup_id')->references('id')->on('superviseur')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Annule les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapport');
}
};
