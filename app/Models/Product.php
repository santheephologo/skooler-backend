<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id',
        'name',
        'description',
        'stock',
        'size',
        'color',
        'price',
        'discount',
        'discounted_price',
        'images',
        'thumbnail',
        'category_id',
        'subcategory_id'
    ];
}
