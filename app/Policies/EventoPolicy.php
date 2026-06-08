<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Evento;

class EventoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Evento $evento): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Evento $evento): bool
    {
        return $evento->created_by === $user->id;
    }

    public function delete(User $user, Evento $evento): bool
    {
        return $evento->created_by === $user->id;
    }
}
