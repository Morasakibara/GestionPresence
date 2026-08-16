<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('presence', function (Blueprint $table) {
            $table->text('rendement')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('presence', function (Blueprint $table) {
            $table->dropColumn('rendement');
        });
    }
};
