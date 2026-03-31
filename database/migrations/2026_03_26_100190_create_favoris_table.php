<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('activite_id')->constrained('activites')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'activite_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoris');
    }
};
