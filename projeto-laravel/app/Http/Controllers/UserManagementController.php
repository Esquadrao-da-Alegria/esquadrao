<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{

    public function index(): Response
    {

        $users = User::with('roles')->get();

        $roles = Role::all();

        return Inertia::render('UserManagement/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }


    public function updateRole(Request $request, $userId)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($userId);
        $role = Role::findOrFail($request->role_id);


        $user->syncRoles([$role->name]);

        return redirect()
            ->back()
            ->with('success', 'Role do usuário atualizada com sucesso!');
    }


    public function toggleActive($userId)
    {
        $user = User::findOrFail($userId);

        $user->active = !$user->active;
        $user->save();

        $status = $user->active ? 'ativado' : 'desativado';

        return redirect()
            ->back()
            ->with('success', "Usuário {$status} com sucesso!");
    }
}
