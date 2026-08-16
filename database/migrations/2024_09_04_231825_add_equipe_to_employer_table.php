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
        Schema::table('employer', function (Blueprint $table) {
            // Nullable : un employé peut être créé sans équipe (il hérite de celle
            // de son superviseur via Sup_id), seule la création superviseur la renseigne.
            $table->string('equipe')->nullable()->after('poste');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employer', function (Blueprint $table) {
            //
        });
    }
};
