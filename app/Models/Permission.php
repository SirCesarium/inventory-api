<?php

namespace App\Models;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(AuditObserver::class)]
class Permission extends Model
{
    protected $fillable = ['name'];

    /**
     * Get permission roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
