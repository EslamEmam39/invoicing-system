<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invoice $model): bool
    {
        return $user->isAdmin() || (string) $model->user_id === (string) $user->getKey();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Invoice $model): bool
    {
        return $user->isAdmin() || (string) $model->user_id === (string) $user->getKey();
    }

    public function delete(User $user, Invoice $model): bool
    {
        return $user->isAdmin();
    }

    public function cancel(User $user, Invoice $model): bool
    {
        return $user->isAdmin();
    }
}
