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
        Schema::create('presence', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Sup_id');
            $table->unsignedBigInteger('employerID');
            $table->dateTime('heureArrivee')->nullable();
            $table->dateTime('heureDepart')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();

            $table->foreign('Sup_id')->references('id')->on('superviseur')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('employerID')->references('id')->on('employer')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presence');
    }
};
