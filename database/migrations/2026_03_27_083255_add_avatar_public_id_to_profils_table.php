<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profils', function (Blueprint $table) {
            $table->string('avatar_public_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profils', function (Blueprint $table) {
            $table->dropColumn('avatar_public_id');
        });
    }
};
