<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('litiges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('prestataire_id')->constrained('prestataires')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sujet');
            $table->text('description');
            $table->string('statut', 32)->default('ouvert');
            $table->string('priorite', 32)->default('normale');
            $table->text('resolution')->nullable();
            $table->timestamp('ferme_le')->nullable();
            $table->timestamps();

            $table->index(['statut', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('litiges');
    }
};
