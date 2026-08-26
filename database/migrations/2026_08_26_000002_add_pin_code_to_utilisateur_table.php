<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->string('pin_code', 4)->nullable()->after('avatar');
            $table->boolean('pin_required')->default(false)->after('pin_code');
        });
    }

    public function down(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->dropColumn(['pin_code', 'pin_required']);
        });
    }
};
