<?php

namespace App\Models;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(AuditObserver::class)]
class StockMovement extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['product_id', 'type', 'quantity', 'before_quantity', 'after_quantity', 'reason'];

    /**
     * Get the product this movement belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
