<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $table = 'reviews';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id',
        'rating',
        'comment',
        'product_id',
        'product_name',
        'user_id',
        'user_name'
    ];
}
