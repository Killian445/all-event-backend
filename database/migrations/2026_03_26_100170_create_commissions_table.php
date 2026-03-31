<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paiement_id')->constrained('paiements')->cascadeOnDelete();
            $table->foreignId('prestataire_id')->constrained('prestataires')->cascadeOnDelete();
            $table->decimal('montant_plateforme', 10, 2);
            $table->decimal('montant_net_prestataire', 10, 2);
            $table->string('devise', 3)->default('MAD');
            $table->timestamps();

            $table->unique('paiement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
