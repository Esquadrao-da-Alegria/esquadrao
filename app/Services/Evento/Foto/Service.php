<?php

namespace App\Services\Evento\Foto;

// MODELS
use App\Models\Evento;
// FACADES
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
// IMAGES
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;
// HTTP
use Illuminate\Http\UploadedFile;

class Service
{
    public function salvar(Evento $evento, UploadedFile $foto): void
    {
        $caminhoAnterior = $evento->imagem_caminho;
        $caminhoNovo = "eventos/{$evento->id}/" . Str::uuid() . '.jpg';
        $conteudoAnterior = $this->lerArquivo($caminhoAnterior);
        $novoArquivoGravado = false;

        try {
            $imagem = Image::decode($foto)
                ->scaleDown(width: 1200, height: 1200)
                ->encode(new JpegEncoder(quality: 85));

            $novoArquivoGravado = true;

            if (! Storage::disk('public')->put($caminhoNovo, $imagem)) {
                throw new \RuntimeException('Não foi possível armazenar a imagem do evento.');
            }

            DB::transaction(function () use ($evento, $caminhoNovo, $caminhoAnterior) {
                if (! $evento->update(['imagem_caminho' => $caminhoNovo])) {
                    throw new \RuntimeException('Não foi possível vincular a imagem ao evento.');
                }

                if ($caminhoAnterior && ! Storage::disk('public')->delete($caminhoAnterior)) {
                    throw new \RuntimeException('Não foi possível remover a imagem anterior do evento.');
                }
            });
        } catch (\Throwable $exception) {
            $this->restaurarArquivo($caminhoAnterior, $conteudoAnterior);

            if ($novoArquivoGravado && ! Storage::disk('public')->delete($caminhoNovo)) {
                throw new \RuntimeException('Não foi possível remover a nova imagem após uma falha.', previous: $exception);
            }

            throw $exception;
        }
    }

    public function remover(Evento $evento): void
    {
        $caminhoAnterior = $evento->imagem_caminho;

        if (! $caminhoAnterior) {
            return;
        }

        $conteudoAnterior = $this->lerArquivo($caminhoAnterior);

        try {
            DB::transaction(function () use ($evento, $caminhoAnterior) {
                if (! $evento->update(['imagem_caminho' => null])) {
                    throw new \RuntimeException('Não foi possível remover a referência da imagem do evento.');
                }

                if (! Storage::disk('public')->delete($caminhoAnterior)) {
                    throw new \RuntimeException('Não foi possível remover a imagem do evento.');
                }
            });
        } catch (\Throwable $exception) {
            $this->restaurarArquivo($caminhoAnterior, $conteudoAnterior);

            throw $exception;
        }
    }

    public function excluir(Evento $evento): void
    {
        $caminho = $evento->imagem_caminho;
        $conteudoAnterior = $this->lerArquivo($caminho);

        try {
            DB::transaction(function () use ($evento, $caminho) {
                if (! $evento->delete()) {
                    throw new \RuntimeException('Não foi possível excluir o evento.');
                }

                if ($caminho && ! Storage::disk('public')->delete($caminho)) {
                    throw new \RuntimeException('Não foi possível remover a imagem do evento.');
                }
            });
        } catch (\Throwable $exception) {
            $this->restaurarArquivo($caminho, $conteudoAnterior);

            throw $exception;
        }
    }

    private function lerArquivo(?string $caminho): ?string
    {
        if (! $caminho || ! Storage::disk('public')->exists($caminho)) {
            return null;
        }

        $conteudo = Storage::disk('public')->get($caminho);

        if (! is_string($conteudo)) {
            throw new \RuntimeException('Não foi possível ler a imagem do evento.');
        }

        return $conteudo;
    }

    private function restaurarArquivo(?string $caminho, ?string $conteudo): void
    {
        if (! $caminho || $conteudo === null || Storage::disk('public')->exists($caminho)) {
            return;
        }

        if (! Storage::disk('public')->put($caminho, $conteudo)) {
            throw new \RuntimeException('Não foi possível restaurar a imagem anterior do evento.');
        }
    }
}
