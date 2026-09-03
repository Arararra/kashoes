<?php

namespace App\Policies;

use App\Models\CashFlow;
use App\Models\User;

class CashFlowPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, CashFlow $cashFlow): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, CashFlow $cashFlow): bool
    {
        return $this->isAdmin($user) && $cashFlow->order_id === null;
    }

    public function delete(User $user, CashFlow $cashFlow): bool
    {
        return $this->isAdmin($user) && $cashFlow->order_id === null;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }
}
