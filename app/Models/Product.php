<?php

namespace App\Models;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(AuditObserver::class)]
class Product extends Model
{
    protected $fillable = ['sku', 'name', 'description', 'price', 'stock'];

    /**
     * Get product category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
