<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas_periodos_padrao_hospitais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meta_padrao_hospital_id')->constrained('metas_padrao_hospitais')->cascadeOnDelete();
            $table->foreignId('ala_unidade_id')->nullable()->constrained('alas_hospitais')->nullOnDelete();
            $table->unsignedTinyInteger('periodo');
            $table->unsignedSmallInteger('quantidade');
            $table->timestamps();
            $table->unique(['meta_padrao_hospital_id', 'ala_unidade_id', 'periodo'], 'mpph_periodo_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas_periodos_padrao_hospitais');
    }
};
