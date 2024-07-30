<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->id = static::getNewEntryId();
        });
    }

    public static function getLastUserEntryId()
    {
        $lastEntry = self::latest()->first();

        if ($lastEntry) {
            return $lastEntry->id;
        }

        return null;
    }
    public static function getLastAdminEntryId()
    {
        $lastEntry = Admin::latest()->first();

        if ($lastEntry) {
            return $lastEntry->id;
        }

        return null;
    }
    public static function getNewEntryId()
    {
        $lastAdminId = self::getLastAdminEntryId();
        $lastUserId = self::getLastUserEntryId();
        if ($lastAdminId > $lastUserId) {
            return intval($lastAdminId + 1);
        } else {
            return intval($lastUserId + 1);
        }
    }
    protected $guard = "api";
    protected $table = "users";
    protected $fillable = [
        'school_id',
        'first_name',
        'last_name',
        'student_id',
        'mobile_no',
        'email',
        'address',
        'password',
        'profile_pic',
        'is_active'
    ];

    protected $hidden = [
        'password',
    ];
}
