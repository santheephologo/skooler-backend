<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admin) {
            $admin->id = static::getNewEntryId();
        });
    }

    public static function getLastAdminEntryId()
    {
        $lastEntry = self::latest()->first();

        if ($lastEntry) {
            return $lastEntry->id;
        }

        return null;
    }
    public static function getLastUserEntryId()
    {
        $lastEntry = User::latest()->first();

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
    protected $guard = "admin";
    protected $table = "admins";
    protected $fillable = [
        'school_id',
        'first_name',
        'last_name',
        'email',
        'mobile_no',
        'address',
        'roles',
        'profile_pic',
        'password',
        'is_active'

    ];
    protected $hidden = [
        'password',
    ];
}
