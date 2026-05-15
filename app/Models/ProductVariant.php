<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'current_stock',
    ];

    /**
     * Relación: una variante pertenece a un producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
