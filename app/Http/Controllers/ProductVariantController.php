<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantController extends Controller
{
    public function store(StoreProductVariantRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $data = $request->validated();
        $data['product_id'] = $product->id;

        ProductVariant::create([
            ...$data,
            'current_stock' => 0,
        ]);

        return redirect()->route('products.edit', $product)->with('success', 'Variante creada correctamente.');
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $variant)
    {
        $this->authorize('update', $variant->product);
        $variant->update($request->validated());

        return redirect()->route('products.edit', $variant->product)->with('success', 'Variante actualizada correctamente.');
    }

    public function destroy(ProductVariant $variant)
    {
        $this->authorize('update', $variant->product);
        $variant->delete();

        return redirect()->route('products.edit', $variant->product)->with('success', 'Variante eliminada correctamente.');
    }
}
