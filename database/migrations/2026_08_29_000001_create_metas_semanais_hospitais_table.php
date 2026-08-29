<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas_semanais_hospitais', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hospital_id')
                ->constrained('hospitais', 'id', 'msh_hospital_id_foreign')
                ->restrictOnDelete();

            $table->foreignId('ala_unidade_id')
                ->nullable()
                ->constrained('alas_hospitais', 'id', 'msh_ala_unidade_id_foreign')
                ->nullOnDelete();

            $table->unsignedSmallInteger('ano');
            $table->unsignedTinyInteger('mes');
            $table->unsignedTinyInteger('semana');
            $table->unsignedSmallInteger('quantidade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas_semanais_hospitais');
    }
};
