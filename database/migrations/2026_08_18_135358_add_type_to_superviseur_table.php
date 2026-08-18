<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('superviseur', function (Blueprint $table) {
            $table->string('type_superviseur')->nullable()->after('equipe');
        });
    }

    public function down(): void
    {
        Schema::table('superviseur', function (Blueprint $table) {
            $table->dropColumn('type_superviseur');
        });
    }
};
