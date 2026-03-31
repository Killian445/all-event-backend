<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activite_medias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activite_id')->constrained('activites')->cascadeOnDelete();
            $table->string('url');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activite_medias');
    }
};
