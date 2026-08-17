<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitas_relatorios', function (Blueprint $table) {
            $table->text('unidades_visitadas')->nullable()->after('ala_unidade_id');
        });
    }

    public function down(): void
    {
        Schema::table('visitas_relatorios', function (Blueprint $table) {
            $table->dropColumn('unidades_visitadas');
        });
    }
};
