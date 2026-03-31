<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('panier_id')->nullable()->constrained('paniers')->nullOnDelete();
            $table->string('statut', 32)->default('en_attente_paiement');
            $table->decimal('montant_total', 10, 2);
            $table->string('devise', 3)->default('MAD');
            $table->timestamps();

            $table->index(['user_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
