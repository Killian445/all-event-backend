<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestataire_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestataire_id')->constrained('prestataires')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('libelle', 255)->nullable();
            $table->string('nom_original', 255);
            $table->string('chemin_disque', 512);
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('taille_octets')->nullable();
            $table->timestamps();

            $table->index(['prestataire_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestataire_documents');
    }
};
