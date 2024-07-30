<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;
    protected $table = 'cartitem';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id',
        'user_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'totalPrice'
    ];
}
