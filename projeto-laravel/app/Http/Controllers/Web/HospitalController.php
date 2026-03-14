<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HospitalController extends Controller{
   public function index(){
        $hospitais = Hospital::all();    
        return Inertia::render('Hospitais/Index',compact('hospitais'));
    }

    public function cadastrar(){
        return Inertia::render('Hospitais/CadastrarHospital',[]);
    }

    public function store(Request $request){
        $request->validate([
            'hospital' => 'required|string|max:100',
        ]);
        
        Hospital::create($request->all());
        return redirect()->route('hospitais.index')->with('message','Hospital cadastrado com sucesso!');
    }

    public function destroy (Hospital $hospital ){
        $hospital->delete();
        return redirect()->route('hospitais.index')->with('message', 'Hospital apagado com sucesso!');
    }

    public function edit (Hospital $hospital ){
        return Inertia::render('Hospitais/EditarHospital',compact('hospital'));
    }

    public function update (Request $request, Hospital $hospital){
        $request->validate([
            'hospital' => 'required|string|max:255',
        ]);

        $hospital->update([
            'hospital'=> $request->input('hospital'),
        ]);
        return redirect()->route('hospitais.index')->with('message','Hospital editado com sucesso!');
    }
}
