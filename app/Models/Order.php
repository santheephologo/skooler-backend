<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'sales_history';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id',
        'user_id',
        'products',
        'total_price',
        'order_type',
        'payment_method',
        'bank_slip',
        'order_status',
        'dispatch_datetime',
        'dispatch_address',
        'reviewed'
    ];
}
