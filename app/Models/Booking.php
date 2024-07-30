<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $table = 'bookings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'school_id',
        'event_id',
        'event_name',
        'user_id',
        'tickets',
        'paid',
        'payment_method',
        'bank_slip',
        'status'
    ];
}
