<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visita_participante', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visita_id')
                ->constrained('visitas', 'id', 'visita_participante_visita_id_foreign')
                ->cascadeOnDelete();

            $table->foreignId('voluntario_id')
                ->constrained('users', 'id', 'visita_participante_voluntario_id_foreign')
                ->restrictOnDelete();

            $table->string('tipo_participacao', 50);
            $table->string('papel_na_visita', 50);
            $table->string('status_participacao', 50);

            $table->timestamps();

            $table->unique(['visita_id', 'voluntario_id'], 'visita_participante_visita_voluntario_unique');
            $table->index('status_participacao', 'visita_participante_status_participacao_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visita_participante');
    }
};
