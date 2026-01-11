<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->department, ['admin', 'inventory']);
    }

    public function update(User $user, Product $product): bool
    {
        return in_array($user->department, ['admin', 'inventory']);
    }

    public function delete(User $user, Product $product): bool
    {
        return in_array($user->department, ['admin', 'inventory']);
    }

    // Admin override
    public function before(User $user, string $ability): ?bool
    {
        if ($user->department === 'admin') {
            return true;
        }
        return null;
    }
}
