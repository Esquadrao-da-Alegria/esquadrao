<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relatorios_visita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visita_id')->constrained('visitas')->restrictOnDelete();
            $table->foreignId('autor_id')->constrained('users')->restrictOnDelete();
            $table->string('tipo_relatorio', 20);
            $table->text('resumo');
            $table->text('feedback')->nullable();
            $table->string('ala_unidade')->nullable();
            $table->unsignedInteger('quartos_visitados')->nullable();
            $table->unsignedInteger('pessoas_impactadas')->nullable();
            $table->text('observacao_visitantes_externos')->nullable();
            $table->text('observacoes_gerais')->nullable();
            $table->timestamp('enviado_em');
            $table->boolean('fora_do_prazo')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relatorios_visita');
    }
};
