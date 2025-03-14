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
        Schema::table('Employer', function (Blueprint $table) {
            $table->rememberToken()->after('poste');
            $table->timestamp('created_at')->nullable()->after('remember_token');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Employer', function (Blueprint $table) {
            $table->dropColumn([ 'remember_token', 'created_at', 'updated_at']);
        });
    }
};
