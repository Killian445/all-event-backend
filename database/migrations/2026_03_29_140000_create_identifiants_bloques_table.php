<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identifiants_bloques', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16);
            $table->string('valeur', 191);
            $table->foreignId('bloque_par_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['type', 'valeur']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identifiants_bloques');
    }
};
