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
        Schema::create('rapport', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Adm_id');
            $table->unsignedBigInteger('Sup_id')->nullable();
            $table->string('periode', 254);
            $table->string('contenu', 254);
            $table->timestamps();

            $table->foreign('Adm_id')->references('id')->on('administrateur')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('Sup_id')->references('id')->on('superviseur')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapport');
    }
};
