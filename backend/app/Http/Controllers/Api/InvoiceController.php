<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexResourceRequest;
use App\Http\Requests\Api\Invoice\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PaginatedResourceCollection;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly InvoiceService $service)
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    public function index(IndexResourceRequest $request): JsonResponse
    {
        $invoices = $this->service->paginate($request->user(), (int) $request->validated('per_page', 15));

        return $this->paginatedResponse(new PaginatedResourceCollection($invoices, InvoiceResource::class));
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->service->create($request->validated(), $request->user());

        return $this->successResponse(new InvoiceResource($invoice), 'Invoice created successfully.', 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return $this->successResponse(new InvoiceResource($this->service->show($invoice)));
    }

    public function cancel(Invoice $invoice): JsonResponse
    {
        $this->authorize('cancel', $invoice);

        return $this->successResponse(
            new InvoiceResource($this->service->cancel($invoice)),
            'Invoice cancelled successfully.',
        );
    }
}
