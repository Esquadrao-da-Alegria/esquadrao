<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas_periodos_hospitais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitais')->restrictOnDelete();
            $table->foreignId('ala_unidade_id')->nullable()->constrained('alas_hospitais')->nullOnDelete();
            $table->unsignedSmallInteger('ano');
            $table->unsignedTinyInteger('mes');
            $table->unsignedTinyInteger('periodo');
            $table->unsignedSmallInteger('quantidade');
            $table->timestamps();
            $table->unique(['hospital_id', 'ala_unidade_id', 'ano', 'mes', 'periodo'], 'mph_periodo_unico');
        });

        DB::table('metas_semanais_hospitais')->orderBy('id')->each(function (object $meta) {
            DB::table('metas_periodos_hospitais')->insert([
                'hospital_id' => $meta->hospital_id,
                'ala_unidade_id' => $meta->ala_unidade_id,
                'ano' => $meta->ano,
                'mes' => $meta->mes,
                'periodo' => $meta->semana,
                'quantidade' => $meta->quantidade,
                'created_at' => $meta->created_at,
                'updated_at' => $meta->updated_at,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas_periodos_hospitais');
    }
};
