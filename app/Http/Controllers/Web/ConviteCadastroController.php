<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Cidade;
use App\Models\ConviteCadastro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ConviteCadastroController extends Controller
{
    public function show(string $token)
    {
        $convite = $this->buscarConvitePorToken($token);
        $validacao = $this->validarConvite($convite);

        return Inertia::render('Convites/CompletarCadastro', [
            'token' => $token,
            'estado' => $validacao['estado'],
            'mensagem' => $validacao['mensagem'],
            'convite' => $validacao['valido'] ? [
                'email' => $convite->email,
                'nome' => $convite->voluntario->nome_completo,
                'nome_doutor' => $convite->voluntario->nome_doutor,
                'telefone' => $convite->voluntario->telefone,
                'data_nascimento' => $convite->voluntario->data_nascimento?->format('Y-m-d'),
                'cpf' => $convite->voluntario->cpf,
                'cidade_base_id' => $convite->voluntario->cidade_base_id,
                'observacoes' => $convite->voluntario->observacoes,
            ] : null,
            'cidades' => $this->cidades(),
        ]);
    }

    public function concluir(Request $request, string $token)
    {
        $convite = $this->buscarConvitePorToken($token);
        $validacao = $this->validarConvite($convite);

        if (! $validacao['valido']) {
            return back()->withErrors([
                'token' => $validacao['mensagem'],
            ]);
        }

        $request->merge([
            'telefone' => $this->formatarTelefone((string) $request->input('telefone', '')),
            'cpf' => $this->formatarCpf((string) $request->input('cpf', '')),
        ]);

        $voluntario = $convite->voluntario;

        $dados = $request->validate([
            'nome_completo' => ['required', 'string', 'max:255'],
            'nome_doutor' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'telefone' => ['required', 'string', 'max:15', 'regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'],
            'data_nascimento' => ['required', 'date', 'before_or_equal:today'],
            'cpf' => [
                'nullable',
                'string',
                'max:14',
                'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                Rule::unique('voluntarios', 'cpf')->ignore($voluntario->id),
            ],
            'cidade_base_id' => ['required', 'integer', 'exists:cidades,id'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required', 'string'],
        ], [
            'nome_completo.required' => 'Informe seu nome completo.',
            'telefone.required' => 'Informe seu telefone.',
            'telefone.regex' => 'Informe um telefone válido com DDD.',
            'data_nascimento.required' => 'Informe sua data de nascimento.',
            'data_nascimento.date' => 'Informe uma data de nascimento válida.',
            'data_nascimento.before_or_equal' => 'A data de nascimento não pode ser futura.',
            'cpf.regex' => 'Informe um CPF válido no formato 000.000.000-00.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'cidade_base_id.required' => 'Selecione sua cidade base.',
            'cidade_base_id.exists' => 'Selecione uma cidade válida.',
            'password.required' => 'Crie uma senha para acessar o sistema.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'password_confirmation.required' => 'Confirme sua senha.',
        ]);

        if ($dados['email'] !== $convite->email) {
            return back()->withErrors([
                'email' => 'O e-mail do convite não pode ser alterado.',
            ]);
        }

        DB::transaction(function () use ($convite, $dados) {
            $voluntario = $convite->voluntario;
            $voluntario->update([
                'nome_completo' => $dados['nome_completo'],
                'nome_doutor' => $dados['nome_doutor'] ?? null,
                'email' => $convite->email,
                'telefone' => $dados['telefone'],
                'data_nascimento' => $dados['data_nascimento'],
                'cpf' => $dados['cpf'] ?: null,
                'cidade_base_id' => $dados['cidade_base_id'],
                'data_entrada_ong' => $voluntario->data_entrada_ong ?? now()->toDateString(),
                'status' => User::STATUS_ATIVO,
                'observacoes' => $dados['observacoes'] ?? null,
            ]);

            $usuario = $voluntario->user ?: User::query()->firstOrNew([
                'email' => $convite->email,
            ]);

            $usuario->fill([
                'voluntario_id' => $voluntario->id,
                'name' => $dados['nome_completo'],
                'email' => $convite->email,
                'password' => Hash::make($dados['password']),
                'status' => User::STATUS_ATIVO,
                'convite_enviado_em' => $convite->enviado_em,
                'convite_expira_em' => $convite->expira_em,
            ]);
            $usuario->email_verified_at ??= now();
            $usuario->save();

            $cargoVoluntario = Cargo::query()->where('slug', 'voluntario')->first();
            if ($cargoVoluntario) {
                $usuario->cargos()->syncWithoutDetaching([$cargoVoluntario->id]);
            }

            $convite->update([
                'status' => ConviteCadastro::STATUS_UTILIZADO,
                'utilizado_em' => now(),
            ]);
        });

        return to_route('login')->with('status', 'Cadastro concluído com sucesso. Entre com seu e-mail e senha.');
    }

    private function buscarConvitePorToken(string $token): ?ConviteCadastro
    {
        return ConviteCadastro::query()
            ->with('voluntario.user')
            ->where('token', hash('sha256', $token))
            ->first();
    }

    private function validarConvite(?ConviteCadastro $convite): array
    {
        if (! $convite || ! $convite->voluntario) {
            return $this->estado('invalido', 'Este convite não foi encontrado ou não está mais disponível.');
        }

        if ($convite->status === ConviteCadastro::STATUS_CANCELADO) {
            return $this->estado('cancelado', 'Este convite foi cancelado e não está mais disponível.');
        }

        if ($convite->status === ConviteCadastro::STATUS_UTILIZADO || $convite->utilizado_em !== null) {
            return $this->estado('utilizado', 'Este convite já foi utilizado. Você já pode entrar com seu e-mail e senha.');
        }

        if ($convite->expirado()) {
            $convite->update(['status' => ConviteCadastro::STATUS_EXPIRADO]);

            return $this->estado('expirado', 'Este convite expirou. Solicite um novo convite à equipe.');
        }

        if ($convite->voluntario->status === User::STATUS_ATIVO && $convite->voluntario->user?->email_verified_at !== null) {
            return $this->estado('utilizado', 'Seu cadastro já está concluído. Entre com seu e-mail e senha.');
        }

        return [
            'valido' => true,
            'estado' => 'valido',
            'mensagem' => null,
        ];
    }

    private function estado(string $estado, string $mensagem): array
    {
        return [
            'valido' => false,
            'estado' => $estado,
            'mensagem' => $mensagem,
        ];
    }

    private function cidades(): array
    {
        return Cidade::query()
            ->select('id', 'nome', 'estado_id')
            ->orderBy('nome')
            ->get()
            ->all();
    }

    private function formatarTelefone(string $telefone): string
    {
        $numeros = preg_replace('/\D/', '', $telefone);

        if (strlen($numeros) === 10) {
            return sprintf(
                '(%s) %s-%s',
                substr($numeros, 0, 2),
                substr($numeros, 2, 4),
                substr($numeros, 6)
            );
        }

        if (strlen($numeros) === 11) {
            return sprintf(
                '(%s) %s-%s',
                substr($numeros, 0, 2),
                substr($numeros, 2, 5),
                substr($numeros, 7)
            );
        }

        return $telefone;
    }

    private function formatarCpf(string $cpf): ?string
    {
        $numeros = preg_replace('/\D/', '', $cpf);

        if ($numeros === '') {
            return null;
        }

        if (strlen($numeros) !== 11) {
            return $cpf;
        }

        return sprintf(
            '%s.%s.%s-%s',
            substr($numeros, 0, 3),
            substr($numeros, 3, 3),
            substr($numeros, 6, 3),
            substr($numeros, 9)
        );
    }
}
