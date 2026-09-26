<?php

namespace App\Services;

use App\Exceptions\ResourceInUseException;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class ProductService
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

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
        if ($model->invoiceItems()->exists()) {
            throw new ResourceInUseException;
        }

        $this->repository->delete($model);
    }
}
