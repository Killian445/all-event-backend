<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages_litige', function (Blueprint $table) {
            $table->id();
            $table->foreignId('litige_id')->constrained('litiges')->cascadeOnDelete();
            $table->foreignId('auteur_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->boolean('interne_admin')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages_litige');
    }
};
