<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('commandes', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('services_fournis', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('retraits', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('services_fournis', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('retraits', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
