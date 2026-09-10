<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_confirmacoes_presenca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('sessao_id')->constrained('evento_sessoes_presenca')->restrictOnDelete();
            $table->foreignId('aberta_por_id')->constrained('users')->restrictOnDelete();
            $table->string('metodo', 30);
            $table->timestamp('confirmada_em');
            $table->timestamps();

            $table->unique(['evento_id', 'user_id']);
            $table->index(['sessao_id', 'confirmada_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_confirmacoes_presenca');
    }
};
