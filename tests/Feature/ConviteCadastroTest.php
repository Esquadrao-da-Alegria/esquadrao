<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\ConviteCadastro;
use App\Models\User;
use App\Models\Voluntario;
use App\Notifications\ConviteCadastroNotification;
use Database\Seeders\CargoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ConviteCadastroTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(CargoSeeder::class);

        $admin = User::factory()->create(['status' => User::STATUS_ATIVO]);
        $admin->cargos()->attach(Cargo::where('slug', 'administrador')->value('id'));

        return $admin;
    }

    private function cidadeId(): int
    {
        DB::table('estados')->insertOrIgnore([
            'id' => 1,
            'nome' => 'Teste',
            'sigla' => 'TS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cidades')->insertOrIgnore([
            'id' => 100,
            'estado_id' => 1,
            'nome' => 'Cidade Teste',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return 100;
    }

    private function criarConvite(string $status = ConviteCadastro::STATUS_ENVIADO, ?\Illuminate\Support\Carbon $expiraEm = null, string $email = 'convidado@example.com'): array
    {
        $voluntario = Voluntario::create([
            'nome_completo' => 'Pessoa Convidada',
            'email' => $email,
            'status' => User::STATUS_CONVITE_ENVIADO,
        ]);

        $user = User::factory()->create([
            'voluntario_id' => $voluntario->id,
            'name' => 'Pessoa Convidada',
            'email' => $email,
            'status' => User::STATUS_CONVITE_ENVIADO,
            'email_verified_at' => null,
        ]);

        $plainToken = 'token-seguro-de-teste-'.md5($email);
        $convite = $voluntario->convitesCadastro()->create([
            'token' => hash('sha256', $plainToken),
            'email' => $email,
            'status' => $status,
            'enviado_em' => now(),
            'expira_em' => $expiraEm ?? now()->addDays(7),
        ]);

        return [$voluntario, $user, $convite, $plainToken];
    }

    public function test_admin_cria_convite_e_notification_e_enviada(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->admin())->post(route('voluntarios.convite.store'), [
            'name' => 'Novo Voluntário',
            'email' => 'novo@example.com',
        ]);

        $response->assertRedirect(route('voluntarios.index', ['aba' => 'convidados']));
        $response->assertSessionHas('mensagem_sucesso', 'Convite enviado com sucesso!');
        Notification::assertSentOnDemand(ConviteCadastroNotification::class);

        $convite = ConviteCadastro::where('email', 'novo@example.com')->firstOrFail();
        $usuario = User::where('email', 'novo@example.com')->firstOrFail();

        $this->assertTrue($usuario->convite_expira_em->equalTo($convite->expira_em));
        $this->assertTrue($usuario->convite_enviado_em->equalTo($convite->enviado_em));
    }

    public function test_nao_admin_nao_cria_reenvia_ou_cancela_convite(): void
    {
        [$voluntario] = $this->criarConvite();
        $naoAdmin = User::factory()->create(['status' => User::STATUS_ATIVO]);

        $this->actingAs($naoAdmin)->post(route('voluntarios.convite.store'), [
            'name' => 'Bloqueado',
            'email' => 'bloqueado@example.com',
        ])->assertForbidden();

        $this->actingAs($naoAdmin)->post(route('voluntarios.convite.reenviar', $voluntario))->assertForbidden();
        $this->actingAs($naoAdmin)->delete(route('voluntarios.convite.cancelar', $voluntario))->assertForbidden();
    }

    public function test_convidado_completa_cadastro_vira_ativo_recebe_cargo_voluntario_e_consegue_logar(): void
    {
        $this->seed(CargoSeeder::class);
        $cidadeId = $this->cidadeId();
        [, $user, $convite, $token] = $this->criarConvite();

        $response = $this->post(route('convites.concluir', $token), [
            'nome_completo' => 'Pessoa Convidada',
            'email' => 'convidado@example.com',
            'telefone' => '(11) 99999-9999',
            'data_nascimento' => '1990-01-01',
            'cpf' => '123.456.789-00',
            'cidade_base_id' => $cidadeId,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login'));

        $user->refresh();
        $convite->refresh();

        $this->assertSame(User::STATUS_ATIVO, $user->status);
        $this->assertTrue($user->temCargo('voluntario'));
        $this->assertSame(ConviteCadastro::STATUS_UTILIZADO, $convite->status);
        $this->assertTrue(Hash::check('password', $user->password));

        $this->post(route('login.store'), [
            'email' => 'convidado@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_convite_expirado_cancelado_ou_utilizado_nao_permite_cadastro(): void
    {
        foreach ([
            [ConviteCadastro::STATUS_ENVIADO, now()->subMinute(), 'expirado@example.com'],
            [ConviteCadastro::STATUS_CANCELADO, now()->addDay(), 'cancelado@example.com'],
            [ConviteCadastro::STATUS_UTILIZADO, now()->addDay(), 'utilizado@example.com'],
        ] as [$status, $expiraEm, $email]) {
            [, , , $token] = $this->criarConvite($status, $expiraEm, $email);

            $this->post(route('convites.concluir', $token), [])->assertSessionHasErrors('token');
        }
    }

    public function test_reenvio_cancela_convite_anterior_e_gera_novo_token(): void
    {
        Notification::fake();
        [$voluntario, $user, $conviteAnterior] = $this->criarConvite();

        $this->actingAs($this->admin())
            ->post(route('voluntarios.convite.reenviar', $voluntario))
            ->assertRedirect(route('voluntarios.index', ['aba' => 'convidados']));

        $conviteAnterior->refresh();
        $novoConvite = $voluntario->convitesCadastro()->latest('id')->first();

        $this->assertSame(ConviteCadastro::STATUS_CANCELADO, $conviteAnterior->status);
        $this->assertNotSame($conviteAnterior->token, $novoConvite->token);
        $this->assertTrue($user->fresh()->convite_expira_em->equalTo($novoConvite->expira_em));
        Notification::assertSentOnDemand(ConviteCadastroNotification::class);
    }
}
