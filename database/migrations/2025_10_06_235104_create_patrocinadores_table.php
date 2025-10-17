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
        Schema::create('patrocinadores', function (Blueprint $table) {
            $table->id();

            $table->string('nome', 255);
            $table->string('site', 100);
            $table->string('categoria', 100);
            $table->string('url_logotipo', 100);
            $table->boolean('ativo', 100);
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrocinadores');
    }
};
