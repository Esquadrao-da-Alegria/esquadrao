<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            $table->foreignId('hospital_id')->nullable()->change();
            $table->unsignedSmallInteger('limite_participantes')->nullable()->default(5)->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            $table->foreignId('hospital_id')->nullable(false)->change();
            $table->dropColumn('limite_participantes');
        });
    }
};
