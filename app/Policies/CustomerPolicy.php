<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'customer']);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->isAdmin($user) || $customer->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->isAdmin($user) || $customer->user_id === $user->id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->isAdmin($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $this->isAdmin($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }
}
