<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_papier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('superviseur_id')->constrained('superviseur', 'id')->onDelete('cascade');
            $table->string('imprimante');
            $table->decimal('metres_restants', 10, 2)->default(0);
            $table->decimal('metres_total', 10, 2)->default(0);
            $table->integer('seuil_alerte')->default(50); // alerte en mètres
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_papier');
    }
};
