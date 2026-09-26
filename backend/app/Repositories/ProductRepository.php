<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Product::query()
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $model, array $data): Product
    {
        $model->update($data);

        return $model->refresh();
    }

    public function delete(Product $model): void
    {
        $model->delete();
    }
}
