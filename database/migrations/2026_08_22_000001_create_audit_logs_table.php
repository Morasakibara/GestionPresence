<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('utilisateur')->nullOnDelete();
            $table->string('role')->nullable();
            $table->string('action');        // ex: 'commande.create', 'retrait.store', 'evaluation.store'
            $table->string('module');         // ex: 'directrice', 'secretaire', 'admin', 'superviseur'
            $table->string('description');    // Description lisible
            $table->json('old_values')->nullable();  // Anciennes valeurs (update/delete)
            $table->json('new_values')->nullable();  // Nouvelles valeurs (create/update)
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['module', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
