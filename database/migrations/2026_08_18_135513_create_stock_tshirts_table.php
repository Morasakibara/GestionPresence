<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_tshirts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('superviseur_id')->constrained('superviseur', 'id')->onDelete('cascade');
            $table->string('couleur');
            $table->string('taille');
            $table->integer('quantite')->default(0);
            $table->integer('seuil_alerte')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_tshirts');
    }
};
