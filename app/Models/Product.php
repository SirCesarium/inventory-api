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

    protected $fillable = ['sku', 'name', 'description', 'price', 'stock', 'category_id', 'barcode', 'minimum_stock'];

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
}
