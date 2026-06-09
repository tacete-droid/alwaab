<?php

namespace App\Services;

use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\StockMovement;
use App\Enums\StockMovementType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function recordMovement(
        string $productId,
        string $warehouseId,
        StockMovementType $type,
        float $quantity,
        User $user,
        ?string $toWarehouseId = null,
        ?string $notes = null,
    ): StockMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(__('messages.invalid_quantity'));
        }

        return DB::transaction(function () use ($productId, $warehouseId, $type, $quantity, $user, $toWarehouseId, $notes) {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['qty_on_hand' => 0, 'qty_reserved' => 0, 'reorder_point' => 0, 'unit' => 'pcs'],
            );

            match ($type) {
                StockMovementType::In, StockMovementType::Adjustment => $inventory->increment('qty_on_hand', $quantity),
                StockMovementType::Out => $this->decrementStock($inventory, $quantity),
                StockMovementType::Transfer => $this->transferStock($inventory, $toWarehouseId, $quantity, $productId),
            };

            return StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'type' => $type,
                'quantity' => $quantity,
                'notes' => $notes,
                'user_id' => $user->id,
            ]);
        });
    }

    private function decrementStock(Inventory $inventory, float $quantity): void
    {
        if ($inventory->qty_on_hand < $quantity) {
            throw new InvalidArgumentException(__('messages.insufficient_stock'));
        }

        $inventory->decrement('qty_on_hand', $quantity);
    }

    public function updateStockLevels(
        Inventory $inventory,
        float $qtyOnHand,
        float $reorderPoint,
        User $user,
        ?string $notes = null,
    ): Inventory {
        if ($qtyOnHand < $inventory->qty_reserved) {
            throw new InvalidArgumentException(__('messages.insufficient_stock'));
        }

        return DB::transaction(function () use ($inventory, $qtyOnHand, $reorderPoint, $user, $notes) {
            $previousQty = (float) $inventory->qty_on_hand;
            $difference = abs($qtyOnHand - $previousQty);

            if ($difference > 0) {
                StockMovement::create([
                    'product_id' => $inventory->product_id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'type' => StockMovementType::Adjustment,
                    'quantity' => $difference,
                    'notes' => $notes ?? __('catalog.stock_adjustment_note', [
                        'from' => $previousQty,
                        'to' => $qtyOnHand,
                    ]),
                    'user_id' => $user->id,
                ]);
            }

            $inventory->update([
                'qty_on_hand' => $qtyOnHand,
                'reorder_point' => $reorderPoint,
            ]);

            return $inventory->fresh();
        });
    }

    private function transferStock(Inventory $from, ?string $toWarehouseId, float $quantity, string $productId): void
    {
        if (! $toWarehouseId) {
            throw new InvalidArgumentException(__('messages.transfer_warehouse_required'));
        }

        $this->decrementStock($from, $quantity);

        $to = Inventory::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $toWarehouseId],
            ['qty_on_hand' => 0, 'qty_reserved' => 0, 'reorder_point' => 0, 'unit' => 'pcs'],
        );

        $to->increment('qty_on_hand', $quantity);
    }
}
