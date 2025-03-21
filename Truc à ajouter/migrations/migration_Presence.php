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
            $table->unsignedBigInteger('Sup_id')->nullable();
            $table->unsignedBigInteger('employerID')->nullable();
            $table->dateTime('heureArrivee')->nullable();
            $table->dateTime('heureDepart')->nullable();
            $table->dateTime('date')->nullable();
            $table->date('updated_at');
            $table->date('created_at');
            $table->string('status');
            $table->double('latitude_arrivee', 10, 7)->nullable();
            $table->double('longitude_arrivee', 10, 7)->nullable();
            $table->double('latitude_depart', 10, 7)->nullable();
            $table->double('longitude_depart', 10, 7)->nullable();
            $table->boolean('localisation_validee_arrivee')->default(false);
            $table->boolean('localisation_validee_depart')->default(false);
            $table->unsignedBigInteger('workplace_location_id')->nullable();

            $table->foreign('Sup_id')->references('id')->on('superviseur')
                  ->onDelete('set null')->onUpdate('cascade');
            $table->foreign('employerID')->references('id')->on('employer')
                  ->onDelete('set null')->onUpdate('cascade');
            $table->foreign('workplace_location_id')->references('id')->on('workplace_locations')
                  ->onDelete('set null')->onUpdate('cascade');
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
