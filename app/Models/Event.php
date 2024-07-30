<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $table = 'event';
    protected $primaryKey = 'id';

    protected $fillable = [
        'school_id',
        'event_name',
        'event_info',
        'venue',
        'capacity',
        'reserved_slots',
        'payment',
        'event_datetime',
        'payment_deadline'


    ];
}
