<?php

namespace App\Services\Hospital\Form;

use Illuminate\Database\Eloquent\Collection;
use App\Services\Cidade\Service as CidadeService;

class Service
{
    public function __construct(
        private CidadeService $cidadeService
    ) {
        //
    }

    public function buscarDados(): array
    {
        return [
            'estados' => $this->cidadeService->index([])
        ];
    }
}
