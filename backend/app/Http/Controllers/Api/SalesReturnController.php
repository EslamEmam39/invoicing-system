<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexResourceRequest;
use App\Http\Requests\Api\SalesReturn\StoreSalesReturnRequest;
use App\Http\Resources\PaginatedResourceCollection;
use App\Http\Resources\SalesReturnResource;
use App\Models\Invoice;
use App\Models\SalesReturn;
use App\Services\SalesReturnService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SalesReturnController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SalesReturnService $service) {}

    public function index(IndexResourceRequest $request): JsonResponse
    {
        $this->authorize('viewAny', SalesReturn::class);
        $returns = $this->service->paginate($request->user(), (int) $request->validated('per_page', 15));

        return $this->paginatedResponse(new PaginatedResourceCollection($returns, SalesReturnResource::class));
    }

    public function show(SalesReturn $salesReturn): JsonResponse
    {
        $this->authorize('view', $salesReturn);

        return $this->successResponse(new SalesReturnResource($this->service->show($salesReturn)));
    }

    public function store(StoreSalesReturnRequest $request): JsonResponse
    {
        $this->authorize('create', SalesReturn::class);
        $invoice = Invoice::query()->findOrFail($request->validated('invoice_id'));
        $this->authorize('view', $invoice);

        return $this->successResponse(
            new SalesReturnResource($this->service->create($invoice, $request->validated())),
            'Sales return created successfully.',
            201,
        );
    }
}
