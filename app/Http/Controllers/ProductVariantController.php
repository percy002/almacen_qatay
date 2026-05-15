<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductVariantController extends Controller
{
    public function store(StoreProductVariantRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $data = $request->validated();
        $data['product_id'] = $product->id;
        $data['image_paths'] = $this->storeUploadedImages($request->file('images', []));

        ProductVariant::create([
            ...$data,
            'current_stock' => 0,
        ]);

        return redirect()->route('products.edit', $product)->with('success', 'Variante creada correctamente.');
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $variant)
    {
        $this->authorize('update', $variant->product);
        $data = $request->validated();
        $originalImages = array_values(array_slice($request->input('original_images', []), 0, 3));
        $currentImages = array_values(array_slice($request->input('current_images', $originalImages), 0, 3));
        $uploadedImages = array_values(array_slice($request->file('images', []), 0, 3));

        $finalImagePaths = [];
        $pathsToDelete = [];

        for ($index = 0; $index < 3; $index++) {
            $originalPath = $originalImages[$index] ?? null;
            $currentPath = $currentImages[$index] ?? null;
            $uploadedFile = $uploadedImages[$index] ?? null;

            if ($uploadedFile instanceof UploadedFile) {
                $finalImagePaths[] = $uploadedFile->store('variants', 'public');

                if ($originalPath) {
                    $pathsToDelete[] = $originalPath;
                }

                continue;
            }

            if (filled($currentPath)) {
                $finalImagePaths[] = $currentPath;

                continue;
            }

            if (filled($originalPath)) {
                $pathsToDelete[] = $originalPath;
            }
        }

        $finalImagePaths = array_values(array_unique(array_filter($finalImagePaths)));

        if ($finalImagePaths === []) {
            throw ValidationException::withMessages([
                'images' => 'Debes conservar o subir al menos una imagen.',
            ]);
        }

        foreach (array_unique($pathsToDelete) as $pathToDelete) {
            Storage::disk('public')->delete($pathToDelete);
        }

        $data['image_paths'] = $finalImagePaths;
        unset($data['current_images'], $data['original_images'], $data['images']);

        $variant->update($data);

        return redirect()->route('products.edit', $variant->product)->with('success', 'Variante actualizada correctamente.');
    }

    public function destroy(ProductVariant $variant)
    {
        $this->authorize('update', $variant->product);

        foreach ($variant->gallery_paths as $pathToDelete) {
            Storage::disk('public')->delete($pathToDelete);
        }

        $variant->delete();

        return redirect()->route('products.edit', $variant->product)->with('success', 'Variante eliminada correctamente.');
    }

    /**
     * @param  array<int, UploadedFile|null>  $images
     * @return array<int, string>
     */
    private function storeUploadedImages(array $images): array
    {
        $storedImages = [];

        foreach (array_slice($images, 0, 3) as $image) {
            if ($image instanceof UploadedFile) {
                $storedImages[] = $image->store('variants', 'public');
            }
        }

        if ($storedImages === []) {
            throw ValidationException::withMessages([
                'images' => 'Debes subir al menos una imagen.',
            ]);
        }

        return $storedImages;
    }
}
