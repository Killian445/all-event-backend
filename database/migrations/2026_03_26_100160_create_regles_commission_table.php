<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regles_commission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('prestataires')->cascadeOnDelete();
            $table->decimal('taux_pourcent', 5, 2);
            $table->date('debut_effet');
            $table->date('fin_effet')->nullable();
            $table->timestamps();

            $table->index(['prestataire_id', 'debut_effet']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regles_commission');
    }
};
