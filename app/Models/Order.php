<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'outlet_id',
        'order_date',
        'order_status',
        'total_products',
        'sub_total',
        'discount',
        'vat',
        'invoice_no',
        'total',
        'payment_type',
        'pay_amount',
        'due_amount',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'sub_total' => 'float',
        'discount' => 'float',
        'vat' => 'float',
        'total' => 'float',
        'pay_amount' => 'float',
        'due_amount' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetails::class);
    }
}
