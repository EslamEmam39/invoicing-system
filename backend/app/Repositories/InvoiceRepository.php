<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceRepository
{
    public function transaction(Closure $callback): mixed
    {
        return DB::transaction($callback, 3);
    }

    public function paginate(User $user, int $perPage): LengthAwarePaginator
    {
        return Invoice::query()->visibleTo($user)->with(['customer', 'items.product'])->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function loadDetails(Invoice $invoice): Invoice
    {
        return $invoice->load(['customer', 'items.product']);
    }

    public function lockById(int $id): Invoice
    {
        return Invoice::query()->lockForUpdate()->findOrFail($id);
    }

    public function hasReturns(Invoice $invoice): bool
    {
        return $invoice->returns()->exists();
    }

    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function update(Invoice $invoice, array $data): void
    {
        $invoice->forceFill($data)->save();
    }
}
