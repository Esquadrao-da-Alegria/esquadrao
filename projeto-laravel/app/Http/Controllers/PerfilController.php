<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function perfil(){
        return Inertia::render('/perfil');
    }
}
