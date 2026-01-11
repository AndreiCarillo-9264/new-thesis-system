<?php

namespace App\Policies;

use App\Models\InventoryTransfer;
use App\Models\User;

class InventoryTransferPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->department === 'logistics';
    }

    public function update(User $user, InventoryTransfer $inventoryTransfer): bool
    {
        return $user->department === 'logistics';
    }

    public function delete(User $user, InventoryTransfer $inventoryTransfer): bool
    {
        return $user->department === 'logistics';
    }

    // Admin override
    public function before(User $user, $ability)
    {
        if ($user->department === 'admin') {
            return true;
        }
    }
}
