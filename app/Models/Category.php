<?php

namespace App\Models;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(AuditObserver::class)]
class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description'];

    /**
     * Get category products
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
