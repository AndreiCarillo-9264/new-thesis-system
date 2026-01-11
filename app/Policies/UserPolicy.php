<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Perform pre-authorization checks.
     * This runs BEFORE any other method in the policy.
     * Gives full access to Admin users for ALL abilities on User model.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->department === 'admin') {
            return true;
        }

        // Return null to continue to specific policy methods
        return null;
    }

    /**
     * Determine whether the user can view ANY users (list/index).
     * Only Admin should see the user management page.
     */
    public function viewAny(User $user): bool
    {
        return false; // Non-admin will be denied here
    }

    /**
     * Determine whether the user can view a specific user.
     * (Rarely used in your case, but good to have)
     */
    public function view(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create new users.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update a user.
     */
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete a user.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Optional: If you ever add restore or forceDelete (soft deletes)
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}