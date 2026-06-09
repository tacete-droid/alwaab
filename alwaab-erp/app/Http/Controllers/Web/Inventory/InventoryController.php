<?php

namespace App\Http\Controllers\Web\Inventory;

use App\Domain\Inventory\Models\Inventory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\UpdateInventoryStockRequest;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('catalog.products.index', $request->only([
            'search', 'warehouse_id', 'low_stock', 'category_id', 'type',
        ]));
    }

    public function updateStock(UpdateInventoryStockRequest $request, Inventory $inventory): RedirectResponse
    {
        $this->inventoryService->updateStockLevels(
            inventory: $inventory,
            qtyOnHand: (float) $request->validated('qty_on_hand'),
            reorderPoint: (float) $request->validated('reorder_point'),
            user: $request->user(),
        );

        return redirect()->back()->with('success', __('messages.updated'));
    }
}
