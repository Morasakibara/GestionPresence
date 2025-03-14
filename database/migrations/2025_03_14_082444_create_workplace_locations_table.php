<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkplaceLocationsTable extends Migration
{
    public function up()
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

    public function down()
    {
        Schema::dropIfExists('workplace_locations');
    }
}
