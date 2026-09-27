<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Product::query()->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Product
    {
        return Product::create($data)->refresh();
    }

    public function update(Product $model, array $data): Product
    {
        $model->update($data);

        return $model->refresh();
    }

    public function hasInvoices(Product $model): bool
    {
        return $model->invoiceItems()->exists();
    }

    public function delete(Product $model): void
    {
        $model->delete();
    }

    public function lockByIds(iterable $ids): Collection
    {
        return Product::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
    }

    public function increaseStock(Product $product, int $quantity): void
    {
        $product->increment('stock', $quantity);
    }

    public function decreaseStock(Product $product, int $quantity): void
    {
        $product->decrement('stock', $quantity);
    }
}
