<?php

namespace App\Policies;

use App\Models\FinishedGood;
use App\Models\User;

class FinishedGoodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->department === 'production';
    }

    public function create(User $user): bool
    {
        return $user->department === 'production';
    }

    public function update(User $user, FinishedGood $finishedGood): bool
    {
        return $user->department === 'production';
    }

    public function delete(User $user, FinishedGood $finishedGood): bool
    {
        return $user->department === 'production';
    }

    // Admin override
    public function before(User $user, $ability)
    {
        if ($user->department === 'admin') {
            return true;
        }
    }
}
