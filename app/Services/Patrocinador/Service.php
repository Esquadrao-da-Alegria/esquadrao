<?php

 ;;5/ewsa   namespace App\Services\Patrocinador;[4]

use App\Queries\Patrocinador\Queries;
use Illuminate\Support\Arr;
 ;;5/ewsa   use Illuminate\Support\Facades\Storage;[4]

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

    //A função store retorna um erro caso a logo do patrocinador seja maior que 2MB, isso acontece porque o método salvarLogotipo é chamado dentro do store, e ele não tem tratamento para arquivos grandes. Para resolver isso, podemos adicionar uma validação no método store para verificar o tamanho do arquivo antes de chamar salvarLogotipo. Se o arquivo for maior que 2MB, podemos retornar um erro específico para o usuário posteriormente.
    public function store(array $dados): array
    {
        try {
            $dadosDatabase = Arr::except($dados, ['logotipo']);

            $id = $this->queries->store($dadosDatabase);

            if (!$id) {
                throw new \Exception("Falha ao salvar no banco de dados.");
            }

            mensagemFlashSalvar(true);

            if (isset($dados['logotipo'])) {
                $retornoLogo = $this->salvarLogotipo(['logotipo' => $dados['logotipo'], 'patrocinador_id' => $id]);
                $this->queries->update((string) $id, ['logo_path' => $retornoLogo['dados']['url'] ?? null]);
            }

            return [
                'sucesso' => true,
                'dados'   => ['id' => $id],
                'erros'   => []
            ];
        } catch (\Throwable $th) {
            dd($th->getMessage());
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

            mensagemFlashSalvar($sucesso);

            if ($logotipo) {
                $retornoLogo = $this->salvarLogotipo(['logotipo' => $logotipo, 'patrocinador_id' => $id]);
                $this->queries->update($id, ['logo_path' => $retornoLogo['dados']['url'] ?? null]);
            }

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
        $patrocinadorId = $dados['patrocinador_id'];

        if (!$logotipo) return ['sucesso' => true, 'dados' => [], 'erros' => []];

        $extensao = "." . $logotipo->getClientOriginalExtension();
        $nomeLogo = "logo-{$patrocinadorId}-" . uniqid() . "$extensao";
        $caminho = "imagens/patrocinadores/{$nomeLogo}";

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk('public');
        $storage->put($caminho, file_get_contents($logotipo));

        return [
            'sucesso' => true,
            'dados'   => ['url' => $storage->url($caminho)],
            'erros'   => []
        ];
    }
}