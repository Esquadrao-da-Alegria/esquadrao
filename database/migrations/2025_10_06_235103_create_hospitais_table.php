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
        Schema::create('hospitais', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cidade_id')->constrained('cidades')->onDelete('cascade');
            $table->string('nome', 255);
            $table->string('cnpj', 14);
            $table->string('endereco', 255);
            $table->string('telefone', 14);
            $table->string('email', 50);
            $table->boolean('ativo')->default(1);
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitais');
    }
};
