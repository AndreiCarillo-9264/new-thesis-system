<?php

namespace App\Policies;

use App\Models\ActualInventory;
use App\Models\User;

class ActualInventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->department === 'inventory';
    }

    public function update(User $user): bool
    {
        return $user->department === 'inventory';
    }

    public function delete(User $user, ActualInventory $actualInventory): bool
    {
        return $user->department === 'inventory';
    }

    // Admin override
    public function before(User $user, $ability)
    {
        if ($user->department === 'admin') {
            return true;
        }
    }
}
