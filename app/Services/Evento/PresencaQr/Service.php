<?php

namespace App\Services\Evento\PresencaQr;

use App\Models\Evento;
use App\Models\EventoConfirmacaoPresenca;
use App\Models\EventoSessaoPresenca;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class Service
{
    public const DURACAO_QR_MINUTOS = 2;

    public function podeGerenciar(User $user, Evento $evento): bool
    {
        return $user->temCargo('administrador')
            || (int) $evento->responsavel_id === (int) $user->id
            || (int) $evento->criado_por_id === (int) $user->id;
    }

    public function abrir(Evento $evento, User $user): EventoSessaoPresenca
    {
        $this->validarAbertura($evento);

        return DB::transaction(function () use ($evento, $user) {
            Evento::query()->whereKey($evento->id)->lockForUpdate()->firstOrFail();
            $ativa = EventoSessaoPresenca::query()->where('evento_id', $evento->id)->whereNull('encerrada_em')->first();

            return $ativa ?: EventoSessaoPresenca::query()->create([
                'evento_id' => $evento->id,
                'aberta_por_id' => $user->id,
                'aberta_em' => now(),
            ]);
        });
    }

    public function encerrar(Evento $evento, EventoSessaoPresenca $sessao, User $user): void
    {
        $this->validarSessaoEvento($evento, $sessao);
        $sessao->update(['encerrada_em' => now(), 'encerrada_por_id' => $user->id]);
    }

    public function encerrarAtivas(Evento $evento, User $user): void
    {
        EventoSessaoPresenca::query()
            ->where('evento_id', $evento->id)->whereNull('encerrada_em')
            ->update(['encerrada_em' => now(), 'encerrada_por_id' => $user->id, 'updated_at' => now()]);
    }

    public function confirmar(EventoSessaoPresenca $sessao, User $user): bool
    {
        $evento = $sessao->evento;
        $this->validarSessaoAtiva($evento, $sessao);

        return DB::transaction(function () use ($evento, $sessao, $user) {
            $confirmacao = EventoConfirmacaoPresenca::query()->firstOrCreate([
                'evento_id' => $evento->id, 'user_id' => $user->id,
            ], [
                'sessao_id' => $sessao->id, 'aberta_por_id' => $sessao->aberta_por_id,
                'metodo' => 'QR_CODE', 'confirmada_em' => now(),
            ]);

            if (! $confirmacao->wasRecentlyCreated) return false;

            $participacao = DB::table('evento_participantes')
                ->where('evento_id', $evento->id)->where('user_id', $user->id)->first();
            $dados = [
                'status' => 'inscrito', 'cancelado_em' => null,
                'presenca' => 'presente', 'presenca_registrada_em' => now(),
                'presenca_registrada_por_id' => $user->id,
                'observacao_presenca' => 'Presença confirmada via QR Code.',
                'updated_at' => now(),
            ];

            if ($participacao) {
                DB::table('evento_participantes')->where('id', $participacao->id)->update($dados);
            } else {
                DB::table('evento_participantes')->insert([
                    'evento_id' => $evento->id, 'user_id' => $user->id,
                    'inscrito_em' => now(), 'created_at' => now(), ...$dados,
                ]);
            }

            return true;
        });
    }

    public function dados(EventoSessaoPresenca $sessao): array
    {
        $url = URL::temporarySignedRoute('eventos.presencas-qr.acesso', now()->addMinutes(self::DURACAO_QR_MINUTOS), [
            'evento' => $sessao->evento_id,
            'sessao' => $sessao->id,
        ]);
        $renderer = new ImageRenderer(new RendererStyle(360, 2), new SvgImageBackEnd());

        return [
            'id' => $sessao->id,
            'aberta_em' => $sessao->aberta_em,
            'expira_em' => now()->addMinutes(self::DURACAO_QR_MINUTOS),
            'qr_svg' => (new Writer($renderer))->writeString($url),
            'confirmacoes' => $sessao->confirmacoes()->with('usuario:id,name')->orderBy('confirmada_em')->get()
                ->map(fn ($confirmacao) => ['id' => $confirmacao->id, 'nome' => $confirmacao->usuario->name, 'confirmada_em' => $confirmacao->confirmada_em]),
        ];
    }

    public function validarSessaoAtiva(Evento $evento, EventoSessaoPresenca $sessao): void
    {
        $this->validarSessaoEvento($evento, $sessao);

        if ($sessao->encerrada_em || ! $evento->estaAgendado()) abort(410, 'Esta confirmação de presença foi encerrada.');
    }

    private function validarAbertura(Evento $evento): void
    {
        if (! in_array($evento->tipo, ['oficina', 'reuniao'], true)) {
            throw ValidationException::withMessages(['evento' => 'A confirmação por QR Code está disponível somente para oficinas e reuniões.']);
        }

        if (! $evento->estaAgendado()) {
            throw ValidationException::withMessages(['evento' => 'Somente eventos agendados permitem abrir a confirmação de presença.']);
        }

        if (! $evento->data_inicio->isToday() || now()->lt($evento->data_inicio->copy()->subHour())) {
            throw ValidationException::withMessages(['evento' => 'A confirmação pode ser aberta no dia do evento, a partir de uma hora antes do início.']);
        }
    }

    private function validarSessaoEvento(Evento $evento, EventoSessaoPresenca $sessao): void
    {
        if ((int) $sessao->evento_id !== (int) $evento->id) abort(404);
    }
}
