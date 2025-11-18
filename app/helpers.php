<?php

if (!function_exists('formatarMensagemErro')) {
    function formatarMensagemErro(\Throwable $th): string
    {
        try {

            return $th->getMessage() . ' | ' . $th->getFile() . ' | ' . $th->getLine();
        } catch (\Throwable $th) {
            return 'Erro ao formatar mensagem de erro';
        }
    }
}

if (!function_exists('mensagemFlashSalvar')) {
    function mensagemFlashSalvar(bool $sucesso): void
    {
        if (!$sucesso) {

            session()->flash('mensagem_erro', 'Erro ao salvar dados!');

            return;
        }

        session()->flash('mensagem_sucesso', 'Dados salvos com sucesso!');
    }
}
