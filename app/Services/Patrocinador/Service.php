<?php

namespace App\Services\Patrocinador;

use App\Queries\Patrocinador\Queries;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(array $filtros): array
    {
        try {
            $resultado = $this->queries->index($filtros);

            return [
                'sucesso' => true,
                'dados'   => $resultado,
                'erros'   => []
            ];
        } catch (\Throwable $th) {
            session()->flash('mensagem_erro', 'Erro ao listar dados!');
            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function store(array $dados): array
    {
        try {
            $dadosDatabase = Arr::except($dados, ['logotipo']);

            $id = $this->queries->store($dadosDatabase);

            if (!$id) {
                throw new \Exception("Falha ao salvar no banco de dados.");
            }

            if (!empty($dados['logotipo'])) {
                $retornoLogo = $this->salvarLogotipo([
                    'logotipo' => $dados['logotipo'],
                    'patrocinador_id' => $id,
                ]);

                $urlLogo = $retornoLogo['dados']['url'] ?? null;

                if (!$urlLogo) {
                    throw new \Exception('Falha ao gerar URL do logotipo.');
                }

                $atualizouLogo = $this->queries->update((string) $id, ['logo_path' => $urlLogo]);

                if (!$atualizouLogo) {
                    throw new \Exception("Falha ao atualizar logo_path do patrocinador {$id}.");
                }
            }

            mensagemFlashSalvar(true);

            return [
                'sucesso' => true,
                'dados'   => ['id' => $id],
                'erros'   => []
            ];
        } catch (\Throwable $th) {
            mensagemFlashSalvar(false);
            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function update(string $id, array $dados): array
    {
        try {
            $logotipo = $dados['logotipo'] ?? null;
            $dadosDatabase = Arr::except($dados, ['logotipo']);

            $sucesso = $this->queries->update($id, $dadosDatabase);

            if ($logotipo) {
                $retornoLogo = $this->salvarLogotipo([
                    'logotipo' => $logotipo,
                    'patrocinador_id' => $id,
                ]);

                $urlLogo = $retornoLogo['dados']['url'] ?? null;

                if (!$urlLogo) {
                    throw new \Exception('Falha ao gerar URL do logotipo.');
                }

                $sucesso = $this->queries->update($id, ['logo_path' => $urlLogo]) && $sucesso;
            }

            mensagemFlashSalvar($sucesso);

            return [
                'sucesso' => $sucesso,
                'dados'   => [],
                'erros'   => []
            ];
        } catch (\Throwable $th) {
            mensagemFlashSalvar(false);
            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function destroy(string $id): array
    {
        try {
            $sucesso = $this->queries->destroy($id);

            if (!$sucesso) {
                session()->flash('mensagem_erro', 'Erro ao excluir dados!');
            } else {
                session()->flash('mensagem_sucesso', 'Dados excluídos com sucesso!');
            }

            return [
                'sucesso' => $sucesso,
                'dados'   => [],
                'erros'   => []
            ];
        } catch (\Throwable $th) {
            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function salvarLogotipo(array $dados): array
    {
        $logotipo = $dados['logotipo'];

        if (!$logotipo) {
            return ['sucesso' => true, 'dados' => [], 'erros' => []];
        }

        $extensao = $logotipo->getClientOriginalExtension()
            ?: $logotipo->guessExtension()
            ?: 'jpg';

        $extensao = strtolower(trim($extensao, '.'));
        $nomeLogo = 'logo-' . uniqid() . ".{$extensao}";
        $caminho = "patrocinadores/{$nomeLogo}";

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk('public');
        $storage->put($caminho, file_get_contents($logotipo));

        return [
            'sucesso' => true,
            'dados'   => ['url' => "/storage/{$caminho}"],
            'erros'   => []
        ];
    }
}
