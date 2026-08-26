<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presence', function (Blueprint $table) {
            // Index composé pour les WHERE date + status (dashboard admin/superviseur)
            $table->index(['date', 'status'], 'presence_date_status_idx');

            // Index pour WHERE date + employerID (pointage quotidien)
            $table->index(['date', 'employerID'], 'presence_date_employer_idx');

            // Index pour WHERE employerID + date (historique par employé)
            $table->index(['employerID', 'date'], 'presence_employer_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('presence', function (Blueprint $table) {
            $table->dropIndex('presence_date_status_idx');
            $table->dropIndex('presence_date_employer_idx');
            $table->dropIndex('presence_employer_date_idx');
        });
    }
};
