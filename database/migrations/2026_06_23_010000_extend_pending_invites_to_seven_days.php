<?php

use App\Models\ConviteCadastro;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('convites_cadastro')
            ->whereIn('status', [
                ConviteCadastro::STATUS_PENDENTE,
                ConviteCadastro::STATUS_ENVIADO,
                ConviteCadastro::STATUS_EXPIRADO,
            ])
            ->orderBy('id')
            ->each(function (object $convite) {
                $enviadoEm = $convite->enviado_em ?? $convite->created_at;

                if (! $enviadoEm) {
                    return;
                }

                $expiraEm = Carbon::parse($enviadoEm)->addDays(7);
                $status = $convite->status;

                if (
                    $status === ConviteCadastro::STATUS_EXPIRADO
                    && $expiraEm->isFuture()
                ) {
                    $status = ConviteCadastro::STATUS_ENVIADO;
                }

                DB::table('convites_cadastro')
                    ->where('id', $convite->id)
                    ->update([
                        'status' => $status,
                        'expira_em' => $expiraEm,
                    ]);
            });

        DB::table('users')
            ->where('status', User::STATUS_CONVITE_ENVIADO)
            ->whereNotNull('convite_enviado_em')
            ->orderBy('id')
            ->each(function (object $user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'convite_expira_em' => Carbon::parse($user->convite_enviado_em)->addDays(7),
                    ]);
            });
    }

    public function down(): void
    {
        // A validade anterior variava por configuração e não pode ser restaurada com segurança.
    }
};
