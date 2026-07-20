<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\RelatorioVisita\StoreRequest;
use App\Http\Requests\Web\RelatorioVisita\UpdateRequest;
use App\Models\RelatorioVisita;
use App\Models\Visita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\