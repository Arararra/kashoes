<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, User $record): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, User $record): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, User $record): bool
    {
        return $user->hasRole('super_admin') && $user->id !== $record->id;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
