<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas_mensais_hospitais', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hospital_id')
                ->constrained('hospitais', 'id', 'mmh_hospital_id_foreign')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('ano');
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('quantidade');

            $table->timestamps();

            $table->unique(['hospital_id', 'ano', 'mes'], 'mmh_hospital_ano_mes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas_mensais_hospitais');
    }
};
