<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenements_statistiques', function (Blueprint $table) {
            $table->id();
            $table->string('type_evenement', 64);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->foreignId('activite_id')->nullable()->constrained('activites')->nullOnDelete();
            $table->foreignId('ville_id')->nullable()->constrained('villes')->nullOnDelete();
            $table->foreignId('prestataire_id')->nullable()->constrained('prestataires')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignId('campagne_publicitaire_id')->nullable()->constrained('campagnes_publicitaires')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['type_evenement', 'occurred_at']);
            $table->index(['prestataire_id', 'occurred_at']);
            $table->index(['activite_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenements_statistiques');
    }
};
