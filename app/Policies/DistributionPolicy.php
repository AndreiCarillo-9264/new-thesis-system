<?php

namespace App\Policies;

use App\Models\Distribution;
use App\Models\User;

class DistributionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->department, ['sales', 'logistics']);
    }

    public function update(User $user, Distribution $distribution): bool
    {
        return in_array($user->department, ['sales', 'logistics']);
    }

    public function delete(User $user, Distribution $distribution): bool
    {
        return in_array($user->department, ['sales', 'logistics']);
    }

    // Admin override
    public function before(User $user, $ability)
    {
        if ($user->department === 'admin') {
            return true;
        }
    }
}
