<?php

namespace App\Services;

use App\Exceptions\ResourceInUseException;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class CustomerService
{
    public function __construct(private readonly CustomerRepository $repository)
    {
    }

    public function paginate(int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function create(array $data): Customer
    {
        return $this->repository->create($data);
    }

    public function update(Customer $model, array $data): Customer
    {
        return $this->repository->update($model, $data);
    }


    public function delete(Customer $model): void
    {
        if ($model->invoices()->exists()) {
            throw new ResourceInUseException;
        }

        $this->repository->delete($model);
    }
}
