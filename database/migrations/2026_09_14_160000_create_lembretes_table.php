<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lembretes', function (Blueprint $table) {
            $table->id();

            $table->string('atividade_tipo');
            $table->unsignedBigInteger('atividade_id');
            $table->string('tipo');
            $table->dateTime('programado_para');
            $table->string('status')->default('pendente');
            $table->dateTime('processado_em')->nullable();
            $table->dateTime('cancelado_em')->nullable();

            $table->timestamps();

            $table->index(['atividade_tipo', 'atividade_id', 'tipo', 'status']);
            $table->index(['status', 'programado_para']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembretes');
    }
};
