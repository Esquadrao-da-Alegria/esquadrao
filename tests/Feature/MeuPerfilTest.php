<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MeuPerfilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_convidado_e_redirecionado_para_login(): void
    {
        $this->get(route('meu-perfil.edit'))->assertRedirect(route('login'));
    }

    public function test_usuario_sem_voluntario_ve_pagina_sem_dados(): void
    {
        $user = User::factory()->createOne();

        $this->actingAs($user)
            ->get(route('meu-perfil.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MeuPerfil/Edit')
                ->where('voluntario', null)
            );
    }

    public function test_usuario_ve_seus_dados_no_perfil(): void
    {
        [$user, $voluntario] = $this->criarUsuarioVoluntario();

        $this->actingAs($user)
            ->get(route('meu-perfil.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MeuPerfil/Edit')
                ->where('voluntario.id', $voluntario->id)
                ->where('voluntario.nome_completo', $voluntario->nome_completo)
            );
    }

    public function test_usuario_pode_atualizar_dados_pessoais(): void
    {
        [$user, $voluntario] = $this->criarUsuarioVoluntario();

        $this->actingAs($user)
            ->patch(route('meu-perfil.update'), [
                'nome_completo'   => 'Novo Nome Completo',
                'telefone'        => '51999998888',
                'data_nascimento' => '1995-03-15',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('voluntarios', [
            'id'            => $voluntario->id,
            'nome_completo' => 'Novo Nome Completo',
            'telefone'      => '51999998888',
        ]);

        $voluntario->refresh();
        $this->assertEquals('1995-03-15', $voluntario->data_nascimento->format('Y-m-d'));
    }

    public function test_artista_pode_atualizar_nome_doutor(): void
    {
        [$user, $voluntario] = $this->criarUsuarioVoluntario(['nome_doutor' => null]);
        $cargo = Cargo::query()->firstOrCreate(['slug' => 'artista'], ['nome' => 'Artista']);
        $user->cargos()->attach($cargo);
        $user->unsetRelation('cargos');

        $this->actingAs($user)
            ->patch(route('meu-perfil.update'), [
                'nome_completo' => $voluntario->nome_completo,
                'nome_doutor'   => 'Dr. Alegria',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('voluntarios', [
            'id'          => $voluntario->id,
            'nome_doutor' => 'Dr. Alegria',
        ]);
    }

    public function test_nao_artista_nao_pode_alterar_nome_doutor(): void
    {
        [$user, $voluntario] = $this->criarUsuarioVoluntario(['nome_doutor' => 'Dr. Original']);

        $this->actingAs($user)
            ->patch(route('meu-perfil.update'), [
                'nome_completo' => $voluntario->nome_completo,
                'nome_doutor'   => 'Dr. Invasor',
            ])
            ->assertRedirect();

        $voluntario->refresh();
        $this->assertEquals('Dr. Original', $voluntario->nome_doutor);
    }

    public function test_nome_completo_e_obrigatorio(): void
    {
        [$user] = $this->criarUsuarioVoluntario();

        $this->actingAs($user)
            ->patch(route('meu-perfil.update'), [
                'nome_completo' => '',
            ])
            ->assertSessionHasErrors('nome_completo');
    }

    public function test_usuario_nao_pode_alterar_campos_administrativos(): void
    {
        [$user, $voluntario] = $this->criarUsuarioVoluntario(['status' => 'ativo']);

        $this->actingAs($user)->patch(route('meu-perfil.update'), [
            'nome_completo'  => 'Nome Teste',
            'status'         => 'inativo',
            'cidade_base_id' => 999,
            'observacoes'    => 'tentativa maliciosa',
            'cpf'            => '000.000.000-00',
        ]);

        $voluntario->refresh();
        $this->assertEquals('ativo', $voluntario->status);
        $this->assertNull($voluntario->cidade_base_id);
        $this->assertNull($voluntario->observacoes);
        $this->assertNull($voluntario->cpf);
    }

    public function test_usuario_pode_fazer_upload_de_foto(): void
    {
        Storage::fake('public');
        [$user, $voluntario] = $this->criarUsuarioVoluntario();

        $foto = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $this->actingAs($user)
            ->post(route('meu-perfil.foto.update'), ['foto' => $foto])
            ->assertRedirect();

        $voluntario->refresh();
        $this->assertNotNull($voluntario->foto_perfil);
        Storage::disk('public')->assertExists($voluntario->foto_perfil);
    }

    public function test_upload_de_arquivo_invalido_retorna_erro(): void
    {
        [$user] = $this->criarUsuarioVoluntario();
        $arquivo = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('meu-perfil.foto.update'), ['foto' => $arquivo])
            ->assertSessionHasErrors('foto');
    }

    public function test_upload_substitui_foto_anterior(): void
    {
        Storage::fake('public');
        [$user, $voluntario] = $this->criarUsuarioVoluntario();

        $caminhoAnterior = "voluntarios/{$voluntario->id}/antiga.jpg";
        Storage::disk('public')->put($caminhoAnterior, 'conteudo');
        $voluntario->update(['foto_perfil' => $caminhoAnterior]);

        $novaFoto = UploadedFile::fake()->image('nova.jpg', 200, 200);
        $this->actingAs($user)
            ->post(route('meu-perfil.foto.update'), ['foto' => $novaFoto])
            ->assertRedirect();

        Storage::disk('public')->assertMissing($caminhoAnterior);
        $voluntario->refresh();
        $this->assertNotNull($voluntario->foto_perfil);
        $this->assertNotEquals($caminhoAnterior, $voluntario->foto_perfil);
    }

    public function test_usuario_pode_remover_foto(): void
    {
        Storage::fake('public');
        [$user, $voluntario] = $this->criarUsuarioVoluntario();

        $caminho = "voluntarios/{$voluntario->id}/foto.jpg";
        Storage::disk('public')->put($caminho, 'conteudo');
        $voluntario->update(['foto_perfil' => $caminho]);

        $this->actingAs($user)
            ->delete(route('meu-perfil.foto.destroy'))
            ->assertRedirect();

        $voluntario->refresh();
        $this->assertNull($voluntario->foto_perfil);
        Storage::disk('public')->assertMissing($caminho);
    }

    private function criarUsuarioVoluntario(array $atributosVoluntario = []): array
    {
        $voluntario = Voluntario::query()->create(array_merge([
            'nome_completo' => 'Voluntário Teste',
            'email'         => 'voluntario@teste.com',
            'status'        => 'ativo',
        ], $atributosVoluntario));

        $user = User::factory()->createOne(['voluntario_id' => $voluntario->id]);

        return [$user, $voluntario];
    }
}