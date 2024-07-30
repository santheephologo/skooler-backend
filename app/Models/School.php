<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;
    protected $primaryKey = "id";
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'schools';
    protected $fillable = [
        'id',
        'name',
        'address',
        'country',
        'country_code',
        'currency',
        'phone',
        'email',
        'ui',
        'is_active',
        'subscription_expiry',
        'delivery',
        'pickup',
        'logo'


    ];
}
