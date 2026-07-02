<?php

namespace App\Services\Hospital;

use App\Queries\Hospital\Queries;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

            DB::beginTransaction();

            $dadosDatabase = Arr::except($dados, ['foto', 'alas']);
            $alas = collect($dados['alas'] ?? [])
                ->map(fn($ala) => is_array($ala) ? ($ala['nome'] ?? null) : $ala)
                ->map(fn($ala) => trim((string) $ala))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $retorno = $this->queries->store($dadosDatabase);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar dados!');

                DB::rollBack();

                return $retorno;
            }

            $id = $retorno['dados']['id'];

            $model = $retorno['dados']['model'];

            if (!empty($alas)) {
                $model->alas()->createMany(
                    array_map(fn($nome) => ['nome' => $nome], $alas)
                );
            }

            $retornoFoto = $this->salvarFoto(['foto' => $dados['foto'], 'hospital_id' => $id]);

            $retorno = $this->queries->update($id, ['url_foto' => $retornoFoto['dados']['url'] ?? null]);

            session()->flash('mensagem_sucesso', 'Dados salvos com sucesso!');

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao salvar dados!');

            DB::rollBack();

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

            DB::beginTransaction();

            $alas = collect($dados['alas'] ?? [])
                ->map(fn($ala) => is_array($ala) ? ($ala['nome'] ?? null) : $ala)
                ->map(fn($ala) => trim((string) $ala))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $foto = $dados['foto'] ?? null;

            $dadosDatabase = Arr::except($dados, ['foto', 'alas']);

            $retorno = $this->queries->update($id, $dadosDatabase);

            if (!$retorno['sucesso']) {

                session()->flash('mensagem_erro', 'Erro ao salvar dados!');

                DB::rollBack();

                return $retorno;
            }

            $model = $retorno['dados']['model'];

            if (empty($alas)) {
                $model->alas()->delete();
            } else {
                // Remover alas que nao estao mais na lista
                $model->alas()->whereNotIn('nome', $alas)->delete();

                // Atualizar ou criar alas, pelo nome
                foreach ($alas as $alaNome) {
                    $model->alas()->updateOrCreate(
                        ['nome' => $alaNome],
                        ['nome' => $alaNome]
                    );
                }
            }

            $retornoFoto = $this->salvarFoto(['foto' => $foto, 'hospital_id' => $id]);

            $this->queries->update($id, ['url_foto' => $retornoFoto['dados']['url'] ?? null]);

            session()->flash('mensagem_sucesso', 'Dados salvos com sucesso!');

            DB::commit();

            return $retorno;
        } catch (\Throwable $th) {

            session()->flash('mensagem_erro', 'Erro ao salvar dados!');

            DB::rollBack();

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

    public function buscarListaAgrupadaPorCidades(): array
    {
        $retornoDatabase = $this->queries->index(['retornar_lista' => true]);

        $lista = $retornoDatabase['dados'];

        // Mapeia ID → slug da cidade como no seu front
        $mapaCidades = [
            4314902 => 'porto_alegre', // Porto Alegre
            4304606 => 'canoas', // Canoas
            4318705 => 'sao_leopoldo', // São Leopoldo
            4316907 => 'santa_maria',  // Santa Maria
            4314407 => 'pelotas',     // Pelotas
        ];

        // Resultado final
        $agrupado = [];

        foreach ($lista as $hospital) {
            $slugCidade = $mapaCidades[$hospital->cidade_id] ?? null;

            if (!$slugCidade) {
                continue; // ignora cidades não mapeadas
            }

            $agrupado[$slugCidade][] = $hospital;
        }

        return $agrupado;
    }
}
