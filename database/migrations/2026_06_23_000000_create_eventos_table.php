<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('tipo');
            $table->text('descricao')->nullable();
            $table->string('local')->nullable();
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();
            $table->unsignedInteger('limite_participantes')->nullable();
            $table->string('status')->default('agendado');
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('criado_por_id')->constrained('users')->cascadeOnDelete();
            $table->text('motivo_cancelamento')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->foreignId('cancelado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
