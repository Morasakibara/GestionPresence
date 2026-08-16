<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReponseContestationToPresenceTable extends Migration
{
    public function up()
    {
        Schema::table('presence', function (Blueprint $table) {
            // Réponse de l'admin à une contestation : statut, commentaire, date
            $table->string('reponse_contestation')->nullable(); // 'accordé' | 'refusé'
            $table->text('commentaire_reponse_contestation')->nullable();
            $table->timestamp('reponse_contestation_le')->nullable();
        });
    }

    public function down()
    {
        Schema::table('presence', function (Blueprint $table) {
            $table->dropColumn([
                'reponse_contestation',
                'commentaire_reponse_contestation',
                'reponse_contestation_le',
            ]);
        });
    }
}
