<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->timestamp('finalizado_em')->nullable()->after('cancelado_por_id');
            $table->foreignId('finalizado_por_id')->nullable()->constrained('users')->nullOnDelete()->after('finalizado_em');
            $table->text('observacoes_finalizacao')->nullable()->after('finalizado_por_id');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['finalizado_por_id']);
            $table->dropColumn(['finalizado_em', 'finalizado_por_id', 'observacoes_finalizacao']);
        });
    }
};
