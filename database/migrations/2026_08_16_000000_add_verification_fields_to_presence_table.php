<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerificationFieldsToPresenceTable extends Migration
{
    public function up()
    {
        Schema::table('presence', function (Blueprint $table) {
            // Précision GPS déclarée par le navigateur (mètres)
            $table->double('accuracy_arrivee')->nullable();
            $table->double('accuracy_depart')->nullable();
            // Horodatage client (secondes Unix)
            $table->bigInteger('client_timestamp_arrivee')->nullable();
            $table->bigInteger('client_timestamp_depart')->nullable();
            // Distance parcourue entre arrivée et départ + vitesse estimée
            $table->double('distance_km')->nullable();
            $table->double('vitesse_kmh')->nullable();
            // Signalement de comportement suspect (calculé par le serveur)
            $table->boolean('suspect')->default(false);
            $table->string('motif_suspicion')->nullable();
        });
    }

    public function down()
    {
        Schema::table('presence', function (Blueprint $table) {
            $table->dropColumn([
                'accuracy_arrivee',
                'accuracy_depart',
                'client_timestamp_arrivee',
                'client_timestamp_depart',
                'distance_km',
                'vitesse_kmh',
                'suspect',
                'motif_suspicion',
            ]);
        });
    }
}
