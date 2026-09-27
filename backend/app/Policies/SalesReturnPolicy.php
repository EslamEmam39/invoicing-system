<?php

namespace App\Policies;

use App\Models\SalesReturn;
use App\Models\User;

class SalesReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SalesReturn $model): bool
    {
        return $user->isAdmin() || $model->invoice()->where('user_id', $user->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }
}
