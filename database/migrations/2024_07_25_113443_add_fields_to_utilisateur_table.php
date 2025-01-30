<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUtilisateurTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Utilisateur', function (Blueprint $table) {
            $table->rememberToken()->after('role');
            $table->timestamp('created_at')->nullable()->after('remember_token');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Utilisateur', function (Blueprint $table) {
            $table->dropColumn([ 'remember_token', 'created_at', 'updated_at']);
        });
    }
}
