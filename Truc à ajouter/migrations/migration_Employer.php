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
        Schema::create('employer', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('Sup_id');
            $table->string('poste', 254)->nullable();
            $table->string('equipe', 255)->default('rienuzg9u7h');
            $table->rememberToken();
            $table->timestamps();

            $table->primary('id');
            $table->foreign('id')->references('id')->on('utilisateur')
                  ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('Sup_id')->references('id')->on('superviseur')
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer');
    }
};
