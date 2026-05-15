<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

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
}
