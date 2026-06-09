<?php

namespace App\Http\Controllers\Web\Inventory;

use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Models\ProductCategory;
use App\Domain\Inventory\Models\Warehouse;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\UpdateProductPriceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $warehouseId = $request->warehouse_id;

        $products = Product::query()
            ->with([
                'category:id,name_ar,name_en,slug,parent_id',
                'inventory' => fn ($q) => $q
                    ->with('warehouse:id,name_ar,name_en')
                    ->when($warehouseId, fn ($q, $id) => $q->where('warehouse_id', $id)),
            ])
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('sku', 'ilike', "%{$search}%")
                    ->orWhere('name_ar', 'ilike', "%{$search}%")
                    ->orWhere('name_en', 'ilike', "%{$search}%");
            }))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->boolean('low_stock'), function ($q) {
                $q->whereHas('inventory', fn ($inv) => $inv->whereColumn('qty_on_hand', '<=', 'reorder_point'));
            })
            ->orderBy('source_sno')
            ->orderBy('sku')
            ->paginate(25)
            ->withQueryString();

        $parent = ProductCategory::where('slug', 'flowguard')->first();

        return Inertia::render('Catalog/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'type', 'category_id', 'warehouse_id', 'low_stock']),
            'types' => collect(ProductType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => __("catalog.types.{$t->value}"),
            ]),
            'catalogTree' => $parent
                ? ProductCategory::where('parent_id', $parent->id)
                    ->orderBy('sort_order')
                    ->get(['id', 'name_ar', 'name_en', 'slug'])
                : collect(),
            'parentCategory' => $parent ? [
                'id' => $parent->id,
                'name_ar' => $parent->name_ar,
                'name_en' => $parent->name_en,
            ] : null,
            'warehouses' => Warehouse::orderBy('name_ar')->get(['id', 'name_ar', 'name_en']),
        ]);
    }

    public function updatePrice(UpdateProductPriceRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        if (! isset($data['price_with_markup_aed'])) {
            $data['price_with_markup_aed'] = round((float) $data['price_aed'] * 1.02, 2);
        }

        $product->update($data);

        return redirect()->back()->with('success', __('messages.updated'));
    }
}
