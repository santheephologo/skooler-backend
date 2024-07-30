<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;
    protected $table = 'complaints';
    protected $primaryKey = 'id';

    protected $fillable = [
        'school_id',
        'order_id',
        'user_id',
        'product_id',
        'product_name',
        'qty',
        'type',
        'description',
        'status',
        'images'
    ];
}
