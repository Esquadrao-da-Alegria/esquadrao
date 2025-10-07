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
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();

            // Relacionamento polimórfico
            $table->unsignedBigInteger('recurso_id');
            $table->string('recurso_tipo');

            // Relacionamento com cidade
            $table->foreignId('cidade_id')->constrained('cidades');

            // Dados do endereço
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep', 8)->nullable();

            $table->timestamps();

            $table->index(['recurso_id', 'recurso_tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enderecos');
    }
};
