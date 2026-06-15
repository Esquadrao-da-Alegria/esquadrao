<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hospital_id')
                ->constrained('hospitais', 'id', 'visitas_hospital_id_foreign')
                ->restrictOnDelete();

            $table->foreignId('ala_unidade_id')
                ->nullable()
                ->constrained('alas_hospitais', 'id', 'visitas_ala_unidade_id_foreign')
                ->nullOnDelete();

            $table->foreignId('criado_por_id')
                ->constrained('users', 'id', 'visitas_criado_por_id_foreign')
                ->restrictOnDelete();

            $table->foreignId('lider_id')
                ->nullable()
                ->constrained('users', 'id', 'visitas_lider_id_foreign')
                ->nullOnDelete();

            $table->timestamp('inicio_em');
            $table->timestamp('fim_em');

            $table->string('tipo', 50);
            $table->string('status', 50);
            $table->string('origem', 50);

            $table->text('observacoes')->nullable();

            $table->timestamps();

            $table->index('inicio_em', 'visitas_inicio_em_index');
            $table->index('status', 'visitas_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
