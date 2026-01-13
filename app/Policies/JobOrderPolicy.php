<?php

namespace App\Policies;

use App\Models\JobOrder;
use App\Models\User;

class JobOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;  // All can view
    }

    public function create(User $user): bool
    {
        return $user->department === 'sales';  // Only Sales create
    }

    public function update(User $user, JobOrder $jobOrder): bool
    {
        return $user->department === 'sales';
    }

    public function delete(User $user, JobOrder $jobOrder): bool
    {
        return $user->department === 'sales';
    }

    // Admin override
    public function before(User $user, $ability)
    {
        if ($user->department === 'admin') {
            return true;
        }
    }
}