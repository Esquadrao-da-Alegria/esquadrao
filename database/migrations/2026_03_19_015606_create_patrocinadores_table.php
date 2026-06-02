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
            $table->uuid('id')->primary();
            $table->string('nome', 255);
            $table->string('site', 100)->nullable();
            $table->string('categoria', 100)->nullable();
            $table->string('logo_path', 100)->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem_exibicao')->default(1);
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
