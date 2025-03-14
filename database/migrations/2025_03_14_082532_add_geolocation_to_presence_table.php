<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGeolocationToPresenceTable extends Migration
{
    public function up()
    {
        Schema::table('presence', function (Blueprint $table) {
            $table->double('latitude_arrivee', 10, 7)->nullable();
            $table->double('longitude_arrivee', 10, 7)->nullable();
            $table->double('latitude_depart', 10, 7)->nullable();
            $table->double('longitude_depart', 10, 7)->nullable();
            $table->boolean('localisation_validee_arrivee')->default(false);
            $table->boolean('localisation_validee_depart')->default(false);
            $table->integer('workplace_location_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('presence', function (Blueprint $table) {
            $table->dropColumn([
                'latitude_arrivee',
                'longitude_arrivee',
                'latitude_depart',
                'longitude_depart',
                'localisation_validee_arrivee',
                'localisation_validee_depart',
                'workplace_location_id'
            ]);
        });
    }
}
