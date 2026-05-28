<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $fillable = ['action', 'user_id', 'auditable_id', 'auditable_type'];

    /**
     * Get the user who executed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent auditable model (Product or Category).
     */
    public function auditable()
    {
        return $this->morphTo();
    }
}
