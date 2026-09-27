<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\SalesReturn;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalesReturnRepository
{
    public function transaction(Closure $callback): mixed
    {
        return DB::transaction($callback, 3);
    }

    public function paginate(User $user, int $perPage): LengthAwarePaginator
    {
        return SalesReturn::query()->visibleTo($user)->with('items')->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function loadDetails(SalesReturn $return): SalesReturn
    {
        return $return->load('items');
    }

    public function create(Invoice $invoice, array $data): SalesReturn
    {
        return $invoice->returns()->create($data);
    }

    public function createItem(SalesReturn $return, array $data): void
    {
        $item = $return->items()->make();
        $item->forceFill($data)->save();
    }

    public function update(SalesReturn $return, array $data): void
    {
        $return->update($data);
    }
}
