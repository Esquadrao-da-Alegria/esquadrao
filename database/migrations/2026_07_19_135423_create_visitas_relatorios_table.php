<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas_relatorios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visita_id')
                ->constrained('visitas', 'id', 'visitas_relatorios_visita_id_foreign')
                ->cascadeOnDelete();

            $table->foreignId('autor_id')
                ->constrained('users', 'id', 'visitas_relatorios_autor_id_foreign')
                ->restrictOnDelete();

            $table->string('tipo_relatorio', 50);
            $table->text('resumo');
            $table->text('feedback')->nullable();
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
        Schema::dropIfExists('visitas_relatorios');
    }
};
