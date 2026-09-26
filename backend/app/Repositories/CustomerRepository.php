<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Customer::query()->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Customer
    {
        return Customer::create($data)->refresh();
    }

    public function update(Customer $model, array $data): Customer
    {
        $model->update($data);

        return $model->refresh();
    }

    public function delete(Customer $model): void
    {
        $model->delete();
    }
}
