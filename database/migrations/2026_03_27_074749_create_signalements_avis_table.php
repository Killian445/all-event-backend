<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signalements_avis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avis_id')->constrained('avis')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('motif', 255);
            $table->text('details')->nullable();
            $table->string('statut', 32)->default('en_attente');
            $table->timestamps();

            $table->unique(['avis_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signalements_avis');
    }
};
