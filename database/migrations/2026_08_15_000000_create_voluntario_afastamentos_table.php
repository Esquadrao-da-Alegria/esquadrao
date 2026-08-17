<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voluntario_afastamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voluntario_id')->constrained('voluntarios')->cascadeOnDelete();
            $table->foreignId('registrado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->string('motivo', 50);
            $table->text('observacoes')->nullable();
            $table->string('status', 30)->default('ativo');
            $table->timestamps();

            $table->index(['voluntario_id', 'status']);
            $table->index(['data_inicio', 'data_fim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voluntario_afastamentos');
    }
};
