<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->decimal('montant', 10, 2);
            $table->string('devise', 3)->default('MAD');
            $table->string('statut', 32)->default('en_attente');
            $table->string('fournisseur', 32)->nullable();
            $table->string('id_intention_fournisseur')->nullable();
            $table->timestamp('paye_le')->nullable();
            $table->timestamps();

            $table->index('reservation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
