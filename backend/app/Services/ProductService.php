<?php

namespace App\Services;

use App\Exceptions\Shared\ResourceInUseException;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(private readonly ProductRepository $repository) {}

    public function paginate(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return $this->repository->create($data);
    }

    public function update(Product $model, array $data): Product
    {
        return $this->repository->update($model, $data);
    }

    public function delete(Product $model): void
    {
        if ($this->repository->hasInvoices($model)) {
            throw new ResourceInUseException;
        }

        $this->repository->delete($model);
    }
}
