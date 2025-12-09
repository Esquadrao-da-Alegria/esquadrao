<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;

class DoutorController extends Controller
{
    public function index(){
        $doutores = Doutor::all();    
        return Inertia::render('Doutores/Index',compact('doutores'));
    }

    public function cadastrar(){
        return Inertia::render('Doutores/CadastrarDoutor',[]);
    }

    public function store(Request $request){
        $request->validate([
            'nome_doutor' => 'required|string|max:50',
        ]);
        
        Doutor::create($request->all());
        return redirect()->route('doutores.index')->with('message','Doutor cadastrado com sucesso!');
    }

    public function destroy (Doutor $doutores ){
        $doutores->delete();
        return redirect()->route('doutores.index')->with('message', 'Doutor apagado com sucesso!');
    }

    public function edit (Doutor $doutores ){
        return Inertia::render('Doutores/EditarDoutor',compact('doutor'));
    }

    public function update (Request $request, Doutor $doutores){
        $request->validate([
            'nome_doutor' => 'required|string|max:50',
        ]);

        $doutores->update([
            'doutor'=> $request->input('doutor'),
        ]);
        return redirect()->route('doutores.index')->with('message','Doutor editado com sucesso!');
    }
}
