<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outlet extends Model
{
    protected $guarded = [];
    
    public function parent():BelongsTo{
        return $this->belongsTo(Outlet::class, 'parent_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'outlet_id', 'id');
    }
}
