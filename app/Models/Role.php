<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
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
