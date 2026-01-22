<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // As an instructor
    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    // As a student
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }
}
