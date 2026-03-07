<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doutores', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('id_user')->constrained('users')->unique()->onDelete('cascade');
            $table->string('nome_doutor');
            $table->string('descricao_doutor')->nullable();
            // ABAIXO, COLUNA PARA IMPLEMENTAÇÃO DE VISIBILIDADE DO PERFIL
            // $table->enum('profile_visibility', ['public', 'private'])->default('public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doutores');
    }
};
