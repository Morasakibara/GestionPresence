<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workplace_locations', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->double('latitude', 10, 7);
            $table->double('longitude', 10, 7);
            $table->integer('rayon')->default(100); // Rayon en mètres
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workplace_locations');
    }
};
