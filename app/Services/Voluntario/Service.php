<?php

namespace App\Services\Voluntario;

use App\Models\Cargo;
use App\Models\User;
use App\Queries\Voluntario\Queries;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
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

            $dadosUsuario = Arr::only($dados, ['name', 'email', 'password']);
            $dadosUsuario['status'] = User::STATUS_ATIVO;

            $retorno = $this->queries->store($dadosUsuario);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar dados!');

                DB::rollBack();

                return $retorno;
            }

            /** @var \App\Models\User $model */
            $model = $retorno['dados']['model'];

            $model->cargos()->sync($cargoIds);

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

            $dadosUsuario = Arr::only($dados, ['name', 'email']);

            if (! empty($dados['password'])) {
                $dadosUsuario['password'] = $dados['password'];
            }

            $retorno = $this->queries->update((string) $id, $dadosUsuario);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar dados!');

                DB::rollBack();

                return $retorno;
            }

            /** @var \App\Models\User $model */
            $model = $retorno['dados']['model'];

            $model->cargos()->sync($cargoIds);

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
                'name' => $dados['name'],
                'email' => $dados['email'],
                'password' => Str::password(32),
                'status' => User::STATUS_CONVITE_ENVIADO,
                'convite_enviado_em' => now(),
                'convite_expira_em' => now()->addMinutes(config('auth.passwords.users.expire', 60)),
            ]);

            if (! $retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao criar convite!');

                DB::rollBack();

                return $retorno;
            }

            /** @var \App\Models\User $model */
            $model = $retorno['dados']['model'];

            $model->cargos()->sync([$cargoVoluntario->id]);
            $this->enviarConvite($model);

            session()->flash('mensagem_sucesso', 'Convite enviado com sucesso!');

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao criar convite!');

            DB::rollBack();

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function reenviarConvite(User $voluntario): array
    {
        try {

            if (! in_array($voluntario->status, [null, User::STATUS_CONVITE_ENVIADO], true)) {

                session()->flash('mensagem_erro', 'Este voluntário não está pendente de convite.');

                return [
                    'sucesso' => false,
                    'dados' => [],
                    'erros' => ['Este voluntário não está pendente de convite.'],
                ];
            }

            $this->enviarConvite($voluntario);

            $voluntario->update([
                'status' => User::STATUS_CONVITE_ENVIADO,
                'convite_enviado_em' => now(),
                'convite_expira_em' => now()->addMinutes(config('auth.passwords.users.expire', 60)),
            ]);

            session()->flash('mensagem_sucesso', 'Convite reenviado com sucesso!');

            return [
                'sucesso' => true,
                'dados' => ['model' => $voluntario],
                'erros' => [],
            ];
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao reenviar convite!');

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    public function cancelarConvite(User $voluntario): array
    {
        try {

            $convitePendente = $voluntario->status === User::STATUS_CONVITE_ENVIADO
                || ($voluntario->status === null && $voluntario->email_verified_at === null);

            if (! $convitePendente) {

                session()->flash('mensagem_erro', 'Este registro não é um convite pendente.');

                return [
                    'sucesso' => false,
                    'dados' => [],
                    'erros' => ['Este registro não é um convite pendente.'],
                ];
            }

            $retorno = $this->queries->delete((string) $voluntario->id);

            if (! $retorno['sucesso']) {
                session()->flash('mensagem_erro', 'Erro ao excluir convite!');

                return $retorno;
            }

            session()->flash('mensagem_sucesso', 'Convite excluído com sucesso!');

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao excluir convite!');

            return [
                'sucesso' => false,
                'dados' => [],
                'erros' => [formatarMensagemErro($th)],
            ];
        }
    }

    private function enviarConvite(User $voluntario): void
    {
        $token = Password::broker()->createToken($voluntario);

        $voluntario->sendPasswordResetNotification($token);
    }
}
