<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\CustomerRequest;
use App\Http\Requests\Api\IndexResourceRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\PaginatedResourceCollection;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CustomerService $service)
    {
        $this->authorizeResource(Customer::class, 'customer');
    }

    public function index(IndexResourceRequest $request): JsonResponse
    {
        $items = $this->service->paginate((int) $request->validated('per_page', 15));

        return $this->paginatedResponse(new PaginatedResourceCollection($items, CustomerResource::class));
    }

    public function store(CustomerRequest $request): JsonResponse
    {
        return $this->successResponse(new CustomerResource($this->service->create($request->validated())), 'Customer created successfully.', 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return $this->successResponse(new CustomerResource($customer));
    }

    public function update(CustomerRequest $request, Customer $customer): JsonResponse
    {
        return $this->successResponse(new CustomerResource($this->service->update($customer, $request->validated())), 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->service->delete($customer);

        return $this->successResponse(message: 'Customer deleted successfully.');
    }
}
