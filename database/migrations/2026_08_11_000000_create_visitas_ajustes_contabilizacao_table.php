<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas_ajustes_contabilizacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visita_id')->constrained('visitas')->restrictOnDelete();
            $table->foreignId('voluntario_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('relatorio_id')->nullable()->constrained('visitas_relatorios')->restrictOnDelete();
            $table->foreignId('administrador_id')->constrained('users')->restrictOnDelete();
            $table->string('tipo', 50);
            $table->string('tipo_participacao', 50)->nullable();
            $table->text('justificativa');
            $table->json('dados_anteriores')->nullable();
            $table->json('dados_posteriores')->nullable();
            $table->timestamps();

            $table->index(['visita_id', 'tipo']);
            $table->unique(['relatorio_id', 'tipo'], 'visita_ajuste_relatorio_tipo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas_ajustes_contabilizacao');
    }
};
