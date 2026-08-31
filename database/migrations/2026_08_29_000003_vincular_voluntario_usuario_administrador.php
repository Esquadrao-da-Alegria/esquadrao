<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL_ADMINISTRADOR = 'esquadraodaalegria.dados@gmail.com';

    private const CIDADE_BASE_PORTO_ALEGRE_ID = 4314902;

    public function up(): void
    {
        $usuario = DB::table('users')
            ->where('email', self::EMAIL_ADMINISTRADOR)
            ->first();

        if (! $usuario) {
            return;
        }

        $cidadeBaseId = DB::table('cidades')
            ->where('id', self::CIDADE_BASE_PORTO_ALEGRE_ID)
            ->exists()
            ? self::CIDADE_BASE_PORTO_ALEGRE_ID
            : null;

        $agora = now();

        $voluntario = DB::table('voluntarios')
            ->where('email', self::EMAIL_ADMINISTRADOR)
            ->first();

        if ($voluntario) {
            $dadosAtualizacao = ['updated_at' => $agora];

            if ($cidadeBaseId !== null) {
                $dadosAtualizacao['cidade_base_id'] = $cidadeBaseId;
            }

            DB::table('voluntarios')
                ->where('id', $voluntario->id)
                ->update($dadosAtualizacao);

            $voluntarioId = $voluntario->id;
        } else {
            $voluntarioId = DB::table('voluntarios')->insertGetId([
                'nome_completo' => $usuario->name,
                'email' => self::EMAIL_ADMINISTRADOR,
                'cidade_base_id' => $cidadeBaseId,
                'status' => $usuario->status ?? User::STATUS_ATIVO,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        }

        if (! $usuario->voluntario_id) {
            DB::table('users')
                ->where('id', $usuario->id)
                ->update(['voluntario_id' => $voluntarioId]);
        }
    }

    public function down(): void
    {
        $usuario = DB::table('users')
            ->where('email', self::EMAIL_ADMINISTRADOR)
            ->first();

        if (! $usuario) {
            return;
        }

        $voluntario = DB::table('voluntarios')
            ->where('email', self::EMAIL_ADMINISTRADOR)
            ->first();

        if ($usuario->voluntario_id && $voluntario && (int) $usuario->voluntario_id === (int) $voluntario->id) {
            DB::table('users')
                ->where('id', $usuario->id)
                ->update(['voluntario_id' => null]);
        }

        if ($voluntario) {
            DB::table('voluntarios')
                ->where('id', $voluntario->id)
                ->delete();
        }
    }
};
