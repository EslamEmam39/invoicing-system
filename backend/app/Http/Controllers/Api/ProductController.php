<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexResourceRequest;
use App\Http\Requests\Api\Product\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ProductService $service)
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(IndexResourceRequest $request): JsonResponse
    {
        $items = $this->service->paginate((int) $request->validated('per_page', 15));

        return $this->successResponse(ProductResource::collection($items)->response()->getData(true));
    }

    public function store(ProductRequest $request): JsonResponse
    {
        return $this->successResponse(new ProductResource($this->service->create($request->validated())), 'Product created successfully.', 201);
    }

    public function show(Product $product): JsonResponse
    {
        return $this->successResponse(new ProductResource($product));
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        return $this->successResponse(new ProductResource($this->service->update($product, $request->validated())), 'Product updated successfully.');
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->service->delete($product);

        return $this->successResponse(message: 'Product deleted successfully.');
    }
}
