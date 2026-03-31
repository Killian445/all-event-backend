<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campagnes_publicitaires', function (Blueprint $table) {
            $table->string('image_url', 1024)->nullable()->after('titre');
        });
    }

    public function down(): void
    {
        Schema::table('campagnes_publicitaires', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
