<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    //lista td mundo
    public function index(): Response
    {
        $users = User::with('role')->get();
        $roles = Role::all();
        
        return Inertia::render('UserManagement/Index', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    //atualizar role
    public function updateRole(Request $request, $userId)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::findOrFail($userId);
        $user->role_id = $request->role_id;
        $user->save();

        return redirect()->back()->with('success', 'Role do usuário atualizado com sucesso!');
    }

    
    //ativar/desativar user
    public function toggleActive($userId)
    {
        $user = User::findOrFail($userId);
        $user->active = !$user->active;
        $user->save();

        $status = $user->active ? 'ativado' : 'desativado';
        return redirect()->back()->with('success', "Usuário {$status} com sucesso!");
    }
}
