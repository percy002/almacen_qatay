<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->internal_code)) {
                $product->internal_code = self::generateUniqueInternalCode($product->name);
            }
        });

        static::updating(function (Product $product): void {
            if (blank($product->internal_code)) {
                $product->internal_code = self::generateUniqueInternalCode($product->name);
            }
        });
    }

    protected $fillable = [
        'name',
        'internal_code',
        'description',
        'min_stock',
        'status',
    ];

    /**
     * Relación: un producto tiene muchas variantes
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    private static function generateUniqueInternalCode(?string $name): string
    {
        $prefix = (string) Str::of($name ?? '')
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '')
            ->substr(0, 4);

        if ($prefix === '') {
            $prefix = 'ITEM';
        }

        $prefix = str_pad($prefix, 4, 'X');

        do {
            $internalCode = 'PRD-'.$prefix.'-'.Str::upper(Str::random(4));
        } while (self::withTrashed()->where('internal_code', $internalCode)->exists());

        return $internalCode;
    }
}
