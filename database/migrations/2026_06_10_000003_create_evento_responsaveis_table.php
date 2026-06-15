<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_responsaveis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evento_id');
            $table->unsignedBigInteger('voluntario_id');
            // Reservado para uso futuro — aguarda definição dos cargos de voluntários
            $table->string('tipo_responsavel', 100)->nullable()->comment('Reservado — não utilizado até definição dos cargos');
            $table->timestamps();

            $table->unique(['evento_id', 'voluntario_id']);
            $table->foreign('evento_id')->references('id')->on('eventos')->onDelete('cascade');
            $table->foreign('voluntario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_responsaveis');
    }
};
