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
        Schema::table('marquer', function (Blueprint $table) {
            $table->dropForeign('marquer_empl_id_foreign');
            $table->foreign('Empl_id')->references('id')->on('employer')
                ->onDelete('cascade')->onUpdate('restrict');
        });

        Schema::table('presence', function (Blueprint $table) {
            $table->dropForeign('presence_employerid_foreign');
            $table->dropForeign('presence_sup_id_foreign');

            $table->foreign('employerID')->references('id')->on('employer')
                ->onDelete('cascade')->onUpdate('restrict');
            $table->foreign('Sup_id')->references('id')->on('superviseur')
                ->onDelete('cascade')->onUpdate('restrict');
        });

        Schema::table('rapport', function (Blueprint $table) {
            $table->dropForeign('rapport_sup_id_foreign');
            $table->foreign('Sup_id')->references('id')->on('superviseur')
                ->onDelete('cascade')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marquer', function (Blueprint $table) {
            $table->dropForeign('marquer_empl_id_foreign');
            $table->foreign('Empl_id')->references('id')->on('employer')
                ->onDelete('restrict')->onUpdate('restrict');
        });

        Schema::table('presence', function (Blueprint $table) {
            $table->dropForeign('presence_employerid_foreign');
            $table->dropForeign('presence_sup_id_foreign');

            $table->foreign('employerID')->references('id')->on('employer')
                ->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('Sup_id')->references('id')->on('superviseur')
                ->onDelete('restrict')->onUpdate('restrict');
        });

        Schema::table('rapport', function (Blueprint $table) {
            $table->dropForeign('rapport_sup_id_foreign');
            $table->foreign('Sup_id')->references('id')->on('superviseur')
                ->onDelete('restrict')->onUpdate('restrict');
        });
    }
};
