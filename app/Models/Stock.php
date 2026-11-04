<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $guarded = [];

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function outlet():BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }
}
