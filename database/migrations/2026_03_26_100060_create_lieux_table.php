<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lieux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ville_id')->constrained('villes')->cascadeOnDelete();
            $table->string('nom');
            $table->string('adresse');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lieux');
    }
};
