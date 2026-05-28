<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
