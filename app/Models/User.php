<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $timestamps = false; // Отключаем created_at и updated_at

    protected $fillable = [
        'username',
        'password_hash',
        'full_name',
        'email',
        'branch_id',
        'position',
        'is_active',
        'last_active_at'
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime'
    ];

    // Указываем поле пароля для Laravel
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}