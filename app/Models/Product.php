<?php

namespace App\Models;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(AuditObserver::class)]
class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['sku', 'name', 'description', 'price', 'category_id', 'barcode', 'minimum_stock'];

    protected $appends = ['stock'];

    /**
     * Get product category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get stock movements for the product.
     */
    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the latest stock movement.
     */
    public function lastMovement()
    {
        return $this->hasOne(StockMovement::class)->latestOfMany();
    }

    /**
     * Compute stock from the latest movement.
     */
    public function getStockAttribute(): int
    {
        return (int) ($this->lastMovement?->after_quantity ?? 0);
    }
}
