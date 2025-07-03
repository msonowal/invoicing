<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'currency',
        'tax_rate',
        'total_amount',
    ];

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
