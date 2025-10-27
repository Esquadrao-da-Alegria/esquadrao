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
