<?php

namespace App\Models;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(AuditObserver::class)]
class Permission extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    /**
     * Get permission roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
