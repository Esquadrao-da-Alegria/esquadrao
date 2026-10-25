<?php

// MIGRATIONS
use Illuminate\Database\Migrations\Migration;

// SCHEMA
use Illuminate\Database\Schema\Blueprint;

// FACADES
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->string('imagem_caminho')->nullable()->after('observacoes_finalizacao');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('imagem_caminho');
        });
    }
};
