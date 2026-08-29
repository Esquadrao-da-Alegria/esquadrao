<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_liberacoes_cidades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cidade_id')
                ->constrained('cidades', 'id', 'alc_cidade_id_foreign')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('ano');
            $table->unsignedTinyInteger('mes');
            $table->boolean('liberado')->default(false);

            $table->foreignId('liberado_por_id')
                ->nullable()
                ->constrained('users', 'id', 'alc_liberado_por_id_foreign')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['cidade_id', 'ano', 'mes'], 'alc_cidade_ano_mes_unique');
        });

        $agora = now();
        $mesInicio = now()->copy()->startOfYear();
        $mesLimite = now()->copy()->addYears(5)->startOfMonth();
        $anoAtual = (int) now()->year;
        $mesAtual = (int) now()->month;

        $cidadeIds = DB::table('cidades')->pluck('id')->all();

        if ($cidadeIds === []) {
            return;
        }

        $registros = [];

        while ($mesInicio <= $mesLimite) {
            $ano = (int) $mesInicio->year;
            $mes = (int) $mesInicio->month;
            $liberado = $ano === $anoAtual && $mes === $mesAtual;

            foreach ($cidadeIds as $cidadeId) {
                $registros[] = [
                    'cidade_id' => $cidadeId,
                    'ano' => $ano,
                    'mes' => $mes,
                    'liberado' => $liberado,
                    'liberado_por_id' => null,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ];

                if (count($registros) >= 1000) {
                    DB::table('agenda_liberacoes_cidades')->insert($registros);
                    $registros = [];
                }
            }

            $mesInicio->addMonth();
        }

        if ($registros !== []) {
            DB::table('agenda_liberacoes_cidades')->insert($registros);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_liberacoes_cidades');
    }
};
