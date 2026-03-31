<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestataires', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('raison_sociale')->nullable();
            $table->string('numero_fiscal')->nullable();
            $table->string('statut', 32)->default('en_attente_validation');
            $table->timestamp('valide_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestataires');
    }
};
