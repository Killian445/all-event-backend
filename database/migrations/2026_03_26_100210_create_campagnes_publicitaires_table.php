<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campagnes_publicitaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->nullable()->constrained('prestataires')->nullOnDelete();
            $table->string('titre');
            $table->string('emplacement', 64);
            $table->foreignId('ville_id')->nullable()->constrained('villes')->nullOnDelete();
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('activite_id')->nullable()->constrained('activites')->nullOnDelete();
            $table->timestamp('debut_at');
            $table->timestamp('fin_at');
            $table->unsignedInteger('priorite')->default(0);
            $table->decimal('budget_montant', 12, 2)->nullable();
            $table->string('statut', 32)->default('brouillon');
            $table->timestamps();

            $table->index(['emplacement', 'statut', 'debut_at', 'fin_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campagnes_publicitaires');
    }
};
