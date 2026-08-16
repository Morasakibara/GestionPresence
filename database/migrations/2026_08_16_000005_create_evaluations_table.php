<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employerID');
            $table->string('mois', 7); // format YYYY-MM
            $table->unsignedTinyInteger('note')->default(10); // note sur 20
            $table->enum('couleur', ['vert', 'orange', 'rouge'])->default('orange');
            $table->text('commentaire')->nullable();
            $table->unsignedBigInteger('evaluateur_id')->nullable();
            $table->timestamps();

            $table->unique(['employerID', 'mois']);
            $table->foreign('employerID')->references('id')->on('employer')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluations');
    }
};
