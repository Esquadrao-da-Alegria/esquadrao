<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('metas_mensais_hospitais', function (Blueprint $table) {
            $table->string('periodicidade', 20)->default('semanal')->after('quantidade');
        });
    }

    public function down(): void
    {
        Schema::table('metas_mensais_hospitais', function (Blueprint $table) {
            $table->dropColumn('periodicidade');
        });
    }
};
