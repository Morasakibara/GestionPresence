<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContestationToPresenceTable extends Migration
{
    public function up()
    {
        Schema::table('presence', function (Blueprint $table) {
            // Contestation par l'employé : commentaire + date de contestation
            $table->text('commentaire_contestation')->nullable();
            $table->timestamp('conteste_le')->nullable();
        });
    }

    public function down()
    {
        Schema::table('presence', function (Blueprint $table) {
            $table->dropColumn(['commentaire_contestation', 'conteste_le']);
        });
    }
}
