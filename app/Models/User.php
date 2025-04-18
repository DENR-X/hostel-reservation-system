<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'password',
        'role',
        'office_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function PaymentExemption()
    {
        return $this->haMany(PaymentExemption::class);
    }

    public function isSuperAdmin()
    {

        if ($this->role === 'super_admin') {
            return true;
        }

        return false;
    }

    public function isSystemAdmin()
    {
        if ($this->role === 'system_admin') {
            return true;
        }

        return false;
    }

    public function hasRole($role)
    {
        if ($this->role === $role) {
            return true;
        }

        return false;
    }
}
