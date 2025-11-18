<?php

namespace App\Services\Hospital;

use App\Queries\Hospital\Queries;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(array $filtros): array
    {
        try {

            $retorno = $this->queries->index($filtros);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao listar dados!');
            }

            return $retorno;
        } catch (\Throwable $th) {
            dd($th);

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

            $dadosDatabase = Arr::except($dados, ['foto']);

            $retorno = $this->queries->store($dadosDatabase);

            mensagemFlashSalvar($retorno['sucesso']);

            $id = $retorno['dados']['id'];

            $retornoFoto = $this->salvarFoto(['foto' => $dados['foto'], 'hospital_id' => $id]);

            $retorno = $this->queries->update($id, ['url_foto' => $retornoFoto['dados']['url'] ?? null]);

            return $retorno;
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function update(int $id, array $dados): array
    {
        try {

            $foto = $dados['foto'] ?? null;

            $dadosDatabase = Arr::except($dados, ['foto']);

            $retorno = $this->queries->update($id, $dadosDatabase);

            mensagemFlashSalvar($retorno['sucesso']);

            $retornoFoto = $this->salvarFoto(['foto' => $foto, 'hospital_id' => $id]);

            $this->update($id, ['url_foto' => $retornoFoto['dados']['url'] ?? null]);

            return $retorno;
        } catch (\Throwable $th) {
            dd($th);

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function destroy(int $id): array
    {
        try {

            $retorno = $this->queries->destroy($id);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao excluir dados!');
            } else {

                session()->flash('mensagem_sucesso', 'Dados excluídos com sucesso!');
            }

            return $retorno;
        } catch (\Throwable $th) {

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)]
            ];
        }
    }

    public function salvarFoto(array $dados): array
    {
        $foto       = $dados['foto'];
        $hospitalId = $dados['hospital_id'];

        if (!$foto) return [
            'sucesso' => true,
            'dados'   => [],
            'erros'   => []
        ];

        $extensao = "." . $foto->getClientOriginalExtension();
        $nomeFoto = "imagem-{$hospitalId}-" . uniqid() . "$extensao";
        $caminho = "imagens/hospitais/{$nomeFoto}";

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk('public');

        $storage->put($caminho, file_get_contents($foto));

        $url = $storage->url($caminho);

        return [
            'sucesso' => true,
            'dados'   => ['url' => $url],
            'erros'   => []
        ];
    }
}
