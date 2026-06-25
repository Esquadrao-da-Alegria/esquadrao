<?php

namespace App\Services\Voluntario;

use App\Models\Cargo;
use App\Models\ConviteCadastro;
use App\Models\User;
use App\Models\Voluntario;
use App\Notifications\ConviteCadastroNotification;
use App\Queries\Voluntario\Queries;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(array $filtros): array
    {
        try {

            $retorno = $this->queries->index($filtros);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao listar dados!');
            }

            return $retorno;
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function store(array $dados): array
    {
        try {

            DB::beginTransaction();

            $cargoIds = array_values(array_unique(array_map('intval', $dados['cargo_ids'] ?? [])));

            $dadosVoluntario = [
                'nome_completo' => $dados['name'],
                'email' => $dados['email'],
                'data_entrada_ong' => now()->toDateString(),
                'status' => User::STATUS_ATIVO,
            ];

            $retorno = $this->queries->store($dadosVoluntario);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar dados!');

                DB::rollBack();

                return $retorno;
            }

            /** @var \App\Models\Voluntario $model */
            $model = $retorno['dados']['model'];

            $usuario = User::create([
                'voluntario_id' => $model->id,
                'name' => $dados['name'],
                'email' => $dados['email'],
                'password' => $dados['password'],
                'status' => User::STATUS_ATIVO,
            ]);

            $usuario->cargos()->sync($cargoIds);

            session()->flash('mensagem_sucesso', 'Dados salvos com sucesso!');

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao salvar dados!');

            DB::rollBack();

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function update(int $id, array $dados): array
    {
        try {

            DB::beginTransaction();

            $cargoIds = array_values(array_unique(array_map('intval', $dados['cargo_ids'] ?? [])));

            $dadosVoluntario = [
                'nome_completo' => $dados['name'],
                'email' => $dados['email'],
            ];

            $retorno = $this->queries->update((string) $id, $dadosVoluntario);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar dados!');

                DB::rollBack();

                return $retorno;
            }

            /** @var \App\Models\Voluntario $model */
            $model = $retorno['dados']['model'];

            $dadosUsuario = Arr::only($dados, ['name', 'email']);

            if (! empty($dados['password'])) {
                $dadosUsuario['password'] = $dados['password'];
            }

            $usuario = $model->user;

            if ($usuario) {
                $usuario->update($dadosUsuario);
            } else {
                $usuario = User::create([
                    ...$dadosUsuario,
                    'voluntario_id' => $model->id,
                    'password' => $dados['password'] ?? Str::password(32),
                    'status' => $model->status,
                ]);
            }

            $usuario->cargos()->sync($cargoIds);

            session()->flash('mensagem_sucesso', 'Dados salvos com sucesso!');

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao salvar dados!');

            DB::rollBack();

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function destroy(int $id): array
    {
        try {

            $retorno = $this->queries->destroy((string) $id);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao excluir dados!');
            } else {
                /** @var \App\Models\Voluntario|null $model */
                $model = $retorno['dados']['model'] ?? null;

                $model?->user?->update([
                    'status' => User::STATUS_INATIVO,
                    'inativado_em' => now(),
                ]);

                session()->flash('mensagem_sucesso', 'Voluntário inativado com sucesso!');
            }

            return $retorno;
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function storeConvite(array $dados): array
    {
        try {

            DB::beginTransaction();

            $cargoVoluntario = Cargo::query()
                ->where('slug', 'voluntario')
                ->firstOrFail();

            $retorno = $this->queries->store([
                'nome_completo' => $dados['name'],
                'email' => $dados['email'],
                'status' => User::STATUS_CONVITE_ENVIADO,
            ]);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao criar convite!');

                DB::rollBack();

                return $retorno;
            }

            /** @var \App\Models\Voluntario $model */
            $model = $retorno['dados']['model'];

            $usuario = User::create([
                'voluntario_id' => $model->id,
                'name' => $dados['name'],
                'email' => $dados['email'],
                'password' => Str::password(32),
                'status' => User::STATUS_CONVITE_ENVIADO,
            ]);

            $usuario->cargos()->sync([$cargoVoluntario->id]);
            $resultadoConvite = $this->criarEnviarConviteCadastro($model, $dados['email']);
            $convite = $resultadoConvite['convite'];

            $usuario->update([
                'convite_enviado_em' => $convite->enviado_em,
                'convite_expira_em' => $convite->expira_em,
            ]);

            if ($resultadoConvite['email_enviado']) {
                session()->flash('mensagem_sucesso', 'Convite enviado com sucesso!');
            } else {
                session()->flash('mensagem_alerta', 'Convite criado, mas o e-mail não foi enviado. Compartilhe o link exibido com o convidado.');
            }

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            Log::error('Erro ao criar convite de voluntário.', [
                'erro' => formatarMensagemErro($th),
                'email' => $dados['email'] ?? null,
            ]);

            session()->flash('mensagem_erro', 'Erro ao criar convite!');

            DB::rollBack();

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function reenviarConvite(Voluntario $voluntario): array
    {
        try {

            $usuario = $voluntario->user;

            if (! $usuario || ! in_array($voluntario->status, [null, User::STATUS_CONVITE_ENVIADO], true)) {

                session()->flash('mensagem_erro', 'Este voluntário não está pendente de convite.');

                return [
                    'sucesso' => false,
                    'dados' => [],
                    'erros' => ['Este voluntário não está pendente de convite.'],
                ];
            }

            DB::beginTransaction();

            $resultadoConvite = $this->criarEnviarConviteCadastro($voluntario, $voluntario->email);
            $convite = $resultadoConvite['convite'];

            $voluntario->update([
                'status' => User::STATUS_CONVITE_ENVIADO,
            ]);

            $usuario->update([
                'status' => User::STATUS_CONVITE_ENVIADO,
                'convite_enviado_em' => $convite->enviado_em,
                'convite_expira_em' => $convite->expira_em,
            ]);

            DB::commit();

            if ($resultadoConvite['email_enviado']) {
                session()->flash('mensagem_sucesso', 'Convite reenviado com sucesso!');
            } else {
                session()->flash('mensagem_alerta', 'Convite reenviado, mas o e-mail não foi enviado. Compartilhe o link exibido com o convidado.');
            }

            return [
                'sucesso' => true,
                'dados' => ['model' => $voluntario],
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            Log::error('Erro ao reenviar convite de voluntário.', [
                'erro' => formatarMensagemErro($th),
                'voluntario_id' => $voluntario->id ?? null,
            ]);

            session()->flash('mensagem_erro', 'Erro ao reenviar convite!');

            DB::rollBack();

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function cancelarConvite(Voluntario $voluntario): array
    {
        try {

            $usuario = $voluntario->user;
            $convitePendente = $voluntario->status === User::STATUS_CONVITE_ENVIADO
                || ($voluntario->status === null && $usuario?->email_verified_at === null);

            if (! $convitePendente) {

                session()->flash('mensagem_erro', 'Este registro não é um convite pendente.');

                return [
                    'sucesso' => false,
                    'dados' => [],
                    'erros' => ['Este registro não é um convite pendente.'],
                ];
            }

            DB::beginTransaction();

            $voluntario->convitesCadastro()
                ->whereIn('status', [
                    ConviteCadastro::STATUS_PENDENTE,
                    ConviteCadastro::STATUS_ENVIADO,
                    ConviteCadastro::STATUS_EXPIRADO,
                ])
                ->update(['status' => ConviteCadastro::STATUS_CANCELADO]);

            $usuario?->update([
                'status' => User::STATUS_INATIVO,
                'inativado_em' => now(),
            ]);

            $retorno = $this->queries->destroy((string) $voluntario->id);

            if (! $retorno['sucesso']) {
                session()->flash('mensagem_erro', 'Erro ao excluir convite!');

                DB::rollBack();

                return $retorno;
            }

            session()->flash('mensagem_sucesso', 'Convite excluído com sucesso!');

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao excluir convite!');

            DB::rollBack();

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    /**
     * @return array{convite: ConviteCadastro, email_enviado: bool}
     */
    private function criarEnviarConviteCadastro(Voluntario $voluntario, string $email): array
    {
        $voluntario->convitesCadastro()
            ->whereIn('status', [
                ConviteCadastro::STATUS_PENDENTE,
                ConviteCadastro::STATUS_ENVIADO,
                ConviteCadastro::STATUS_EXPIRADO,
            ])
            ->update(['status' => ConviteCadastro::STATUS_CANCELADO]);

        $token = Str::random(64);
        $expiraEm = $this->calcularExpiracaoConvite();

        $convite = $voluntario->convitesCadastro()->create([
            'token' => hash('sha256', $token),
            'email' => $email,
            'status' => ConviteCadastro::STATUS_ENVIADO,
            'enviado_em' => now(),
            'expira_em' => $expiraEm,
            'created_by' => auth()->id(),
        ]);

        session()->flash('link_convite', route('convites.show', ['token' => $token]));

        $emailEnviado = true;

        try {
            Notification::route('mail', $email)
                ->notify(new ConviteCadastroNotification($convite, $token));
        } catch (\Throwable $th) {
            $emailEnviado = false;
            Log::warning('Convite criado, mas o e-mail não foi enviado.', [
                'erro' => formatarMensagemErro($th),
                'convite_id' => $convite->id,
                'email' => $email,
                'link' => route('convites.show', ['token' => $token]),
            ]);
        }

        return [
            'convite' => $convite,
            'email_enviado' => $emailEnviado,
        ];
    }

    private function calcularExpiracaoConvite(): \Illuminate\Support\Carbon
    {
        return now()->addDays(7);
    }
}
