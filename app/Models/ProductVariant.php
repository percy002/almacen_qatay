<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (ProductVariant $variant): void {
            if (blank($variant->sku)) {
                $variant->sku = self::generateUniqueSku($variant->product_id);
            }
        });

        static::updating(function (ProductVariant $variant): void {
            if (blank($variant->sku)) {
                $variant->sku = self::generateUniqueSku($variant->product_id);
            }
        });
    }

    protected $fillable = [
        'product_id',
        'variant_name',
        'sku',
        'image_path',
        'image_paths',
        'current_stock',
    ];

    protected $casts = [
        'image_paths' => 'array',
    ];

    protected $appends = [
        'image_url',
        'image_urls',
        'gallery_paths',
    ];

    /**
     * Relación: una variante pertenece a un producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_urls[0] ?? null;
    }

    public function getImageUrlsAttribute(): array
    {
        return array_values(array_filter(array_map(
            fn (string $path): string => Storage::disk('public')->url($path),
            $this->gallery_paths
        )));
    }

    public function getGalleryPathsAttribute(): array
    {
        $paths = array_values(array_filter(Arr::wrap($this->image_paths)));

        if ($paths === [] && filled($this->image_path)) {
            $paths[] = $this->image_path;
        }

        return array_values(array_slice($paths, 0, 3));
    }

    private static function generateUniqueSku(?int $productId): string
    {
        $productInternalCode = Product::query()
            ->whereKey($productId)
            ->value('internal_code');

        $base = (string) Str::of($productInternalCode ?? 'ITEM')
            ->upper()
            ->replaceMatches('/\s+/', '-')
            ->replaceMatches('/[^A-Z0-9\-]/', '')
            ->trim('-');

        if ($base === '') {
            $base = 'ITEM';
        }

        do {
            $sku = 'SKU-'.$base.'-'.Str::upper(Str::random(4));
        } while (self::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }
}
