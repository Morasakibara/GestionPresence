<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services_fournis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('superviseur_id')->constrained('superviseur', 'id')->onDelete('cascade');
            $table->string('type');
            $table->decimal('montant', 10, 2);
            $table->text('details')->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services_fournis');
    }
};
