<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Voluntario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Inertia\Inertia;
use Inertia\Response;

class MeuPerfilController extends Controller
{
    public function edit(Request $request): Response
    {
        $voluntario = $this->voluntario($request)?->load('cidadeBase');

        return Inertia::render('MeuPerfil/Edit', [
            'voluntario' => $voluntario ? [
                'id'             => $voluntario->id,
                'nome_completo'  => $voluntario->nome_completo,
                'nome_doutor'    => $voluntario->nome_doutor,
                'telefone'       => $voluntario->telefone,
                'data_nascimento' => $voluntario->data_nascimento?->format('Y-m-d'),
                'status'         => $voluntario->status,
                'data_entrada_ong' => $voluntario->data_entrada_ong?->format('Y-m-d'),
                'url_foto'       => $voluntario->url_foto,
                'cidade_base'    => $voluntario->cidadeBase
                    ? ['nome' => $voluntario->cidadeBase->nome]
                    : null,
            ] : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $voluntario = $this->voluntario($request);

        if (! $voluntario) {
            return back()->with('mensagem_erro', 'Perfil de voluntário não encontrado.');
        }

        $ehArtista = $request->user()->cargos?->contains('slug', 'artista') ?? false;

        $rules = [
            'nome_completo'   => ['required', 'string', 'max:255'],
            'telefone'        => ['nullable', 'string', 'max:30'],
            'data_nascimento' => ['nullable', 'date', 'before:today'],
        ];

        if ($ehArtista) {
            $rules['nome_doutor'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $voluntario->update($validated);

        return back()->with('mensagem_sucesso', 'Perfil atualizado com sucesso.');
    }

    public function updateFoto(Request $request): RedirectResponse
    {
        $voluntario = $this->voluntario($request);

        if (! $voluntario) {
            return back()->with('mensagem_erro', 'Perfil de voluntário não encontrado.');
        }

        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $fotoAnterior = $voluntario->foto_perfil;

        $caminho = "voluntarios/{$voluntario->id}/" . Str::uuid() . '.jpg';

        $encoded = Image::decode($request->file('foto'))
            ->scaleDown(width: 800, height: 800)
            ->encode(new JpegEncoder(quality: 85));

        Storage::disk('public')->put($caminho, $encoded);
        $voluntario->update(['foto_perfil' => $caminho]);

        if ($fotoAnterior) {
            Storage::disk('public')->delete($fotoAnterior);
        }

        return back()->with('mensagem_sucesso', 'Foto atualizada com sucesso.');
    }

    public function destroyFoto(Request $request): RedirectResponse
    {
        $voluntario = $this->voluntario($request);

        if (! $voluntario) {
            return back()->with('mensagem_erro', 'Perfil de voluntário não encontrado.');
        }

        if ($voluntario->foto_perfil) {
            Storage::disk('public')->delete($voluntario->foto_perfil);
            $voluntario->update(['foto_perfil' => null]);
        }

        return back()->with('mensagem_sucesso', 'Foto removida com sucesso.');
    }

    private function voluntario(Request $request): ?Voluntario
    {
        $id = $request->user()->voluntario_id;

        return $id ? Voluntario::find($id) : null;
    }
}
