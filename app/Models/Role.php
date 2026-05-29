<?php

namespace App\Models;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(AuditObserver::class)]
class Role extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    /**
     * Get role users
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get role permissions
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
