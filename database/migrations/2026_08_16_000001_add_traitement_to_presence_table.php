<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTraitementToPresenceTable extends Migration
{
    public function up()
    {
        // Statut de traitement sur la présence suspecte
        Schema::table('presence', function (Blueprint $table) {
            $table->string('statut_traitement', 20)->default('nouveau')->after('motif_suspicion');
            $table->text('commentaire_traitement')->nullable()->after('statut_traitement');
            $table->unsignedBigInteger('traite_par')->nullable()->after('commentaire_traitement');
            $table->timestamp('traite_le')->nullable()->after('traite_par');
        });

        // Historique des traitements (audit trail)
        Schema::create('presence_traitements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('presence_id');
            $table->string('statut_avant', 20);
            $table->string('statut_apres', 20);
            $table->text('commentaire')->nullable();
            $table->unsignedBigInteger('traite_par')->nullable();
            $table->timestamps();

            $table->foreign('presence_id')->references('id')->on('presence')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('presence_traitements');

        Schema::table('presence', function (Blueprint $table) {
            $table->dropColumn(['statut_traitement', 'commentaire_traitement', 'traite_par', 'traite_le']);
        });
    }
}
