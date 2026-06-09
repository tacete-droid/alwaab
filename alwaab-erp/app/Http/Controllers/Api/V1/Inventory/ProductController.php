<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Support\DatabaseHelper;

use App\Domain\Inventory\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('sku', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhere('name_ar', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhere('name_en', DatabaseHelper::likeOperator(), "%{$search}%");
            }))
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::success(ProductResource::collection($products));
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'inventory.warehouse']);

        return ApiResponse::success(new ProductResource($product));
    }

    public function specs(Product $product): JsonResponse
    {
        return ApiResponse::success([
            'sku' => $product->sku,
            'type' => $product->type->value,
            'size' => $product->size,
            'pressure_rating' => $product->pressure_rating,
            'certifications' => $product->certifications,
            'datasheet_url' => $product->datasheet_url,
            'description' => app()->getLocale() === 'ar' ? $product->description_ar : $product->description_en,
        ]);
    }
}
