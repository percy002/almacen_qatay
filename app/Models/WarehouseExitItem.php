<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseExitItem extends Model
{
    protected $fillable = [
        'warehouse_exit_id',
        'product_variant_id',
        'quantity',
    ];

    public function exit(): BelongsTo
    {
        return $this->belongsTo(WarehouseExit::class, 'warehouse_exit_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
