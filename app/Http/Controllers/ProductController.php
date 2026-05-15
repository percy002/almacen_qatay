<?php

namespace App\Http\Controllers;


use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;


class ProductController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);
        $products = Product::with('variants')->orderBy('name')->paginate(15);
        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }


    public function create()
    {
        $this->authorize('create', Product::class);
        return Inertia::render('Products/Create');
    }


    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);
        $product = Product::create($request->validated());
        return redirect()->route('products.show', $product)->with('success', 'Producto creado correctamente.');
    }


    public function show(Product $product)
    {
        $this->authorize('view', $product);
        $product->load('variants');
        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }


    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        $product->load('variants');
        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }


    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $product->update($request->validated());
        return redirect()->route('products.show', $product)->with('success', 'Producto actualizado correctamente.');
    }


    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Producto eliminado correctamente.');
    }
}
