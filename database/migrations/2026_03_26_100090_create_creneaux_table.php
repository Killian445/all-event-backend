<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creneaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activite_id')->constrained('activites')->cascadeOnDelete();
            $table->dateTime('debut_at');
            $table->dateTime('fin_at');
            $table->unsignedInteger('capacite_totale');
            $table->unsignedInteger('capacite_restante');
            $table->decimal('prix_applique', 10, 2)->nullable();
            $table->string('statut', 32)->default('ouvert');
            $table->timestamps();

            $table->index(['activite_id', 'debut_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creneaux');
    }
};
