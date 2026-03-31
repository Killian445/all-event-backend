<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('prestataires')->cascadeOnDelete();
            $table->foreignId('categorie_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('ville_id')->constrained('villes')->cascadeOnDelete();
            $table->foreignId('lieu_id')->nullable()->constrained('lieux')->nullOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('statut', 32)->default('brouillon');
            $table->decimal('prix_base', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activites');
    }
};
