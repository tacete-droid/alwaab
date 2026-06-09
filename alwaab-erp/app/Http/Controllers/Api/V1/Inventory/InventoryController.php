<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Domain\Inventory\Models\Inventory;
use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreStockMovementRequest;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\StockMovementResource;
use App\Http\Responses\ApiResponse;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function stock(Request $request): JsonResponse
    {
        $stock = Inventory::query()
            ->with(['product', 'warehouse'])
            ->when($request->warehouse_id, fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->boolean('low_stock'), fn ($q) => $q->whereColumn('qty_on_hand', '<=', 'reorder_point'))
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::success(InventoryResource::collection($stock));
    }

    public function storeMovement(StoreStockMovementRequest $request): JsonResponse
    {
        $movement = $this->inventoryService->recordMovement(
            productId: $request->validated('product_id'),
            warehouseId: $request->validated('warehouse_id'),
            type: StockMovementType::from($request->validated('type')),
            quantity: (float) $request->validated('quantity'),
            user: $request->user(),
            toWarehouseId: $request->validated('to_warehouse_id'),
            notes: $request->validated('notes'),
        );

        return ApiResponse::success(
            new StockMovementResource($movement->load(['product', 'warehouse', 'user'])),
            __('messages.created'),
            201
        );
    }
}
