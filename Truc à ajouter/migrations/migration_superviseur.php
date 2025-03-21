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
        Schema::create('superviseur', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('equipe', 254)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->primary('id');
            $table->foreign('id')->references('id')->on('utilisateur')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('superviseur');
    }
};
