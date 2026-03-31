<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->nullable()->unique();
            $table->string('libelle');
            $table->foreignId('prestataire_id')->nullable()->constrained('prestataires')->nullOnDelete();
            $table->foreignId('activite_id')->nullable()->constrained('activites')->nullOnDelete();
            $table->string('type_remise', 32);
            $table->decimal('valeur', 10, 2);
            $table->decimal('montant_minimum_commande', 10, 2)->nullable();
            $table->decimal('reduction_plafond', 10, 2)->nullable();
            $table->unsignedInteger('utilisations_max')->nullable();
            $table->unsignedInteger('utilisations_actuelles')->default(0);
            $table->timestamp('debut_at');
            $table->timestamp('fin_at');
            $table->string('statut', 32)->default('active');
            $table->timestamps();

            $table->index(['statut', 'debut_at', 'fin_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
