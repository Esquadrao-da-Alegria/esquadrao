<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\User;

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
        return $this->eCriadorOuResponsavel($user, $evento);
    }

    public function finalizar(User $user, Evento $evento): bool
    {
        return $this->eCriadorOuResponsavel($user, $evento);
    }

    public function cancelar(User $user, Evento $evento): bool
    {
        return $this->eCriadorOuResponsavel($user, $evento);
    }

    public function delete(User $user, Evento $evento): bool
    {
        return $this->eCriadorOuResponsavel($user, $evento);
    }

    private function eCriadorOuResponsavel(User $user, Evento $evento): bool
    {
        return $user->id === $evento->criado_por_id
            || $evento->responsaveis()->where('voluntario_id', $user->id)->exists();
    }
}
