<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_ajustes_participacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->restrictOnDelete();
            $table->foreignId('voluntario_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('administrador_id')->constrained('users')->restrictOnDelete();
            $table->string('tipo', 50);
            $table->text('justificativa');
            $table->json('dados_anteriores')->nullable();
            $table->json('dados_posteriores');
            $table->timestamps();

            $table->index(['evento_id', 'voluntario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_ajustes_participacao');
    }
};
