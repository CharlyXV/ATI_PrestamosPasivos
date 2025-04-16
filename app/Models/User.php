<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->username)) {
                $user->username = static::generateUsername($user->name);
            }
        });
    }

    public static function generateUsername($fullName)
    {
        $parts = explode(' ', trim($fullName));
        $username = '';
        
        // Primera parte del nombre (capitalizada)
        if (count($parts) > 0) {
            $username .= Str::ucfirst(Str::lower($parts[0]));
        }
        
        // Primera letra de la segunda parte (si existe)
        if (count($parts) > 1) {
            $username .= Str::ucfirst(Str::lower($parts[1][0]));
        }
        
        // Asegurar que primera y última letra sean mayúsculas
        if (!empty($username)) {
            $username[0] = Str::upper($username[0]);
            $lastPos = strlen($username) - 1;
            $username[$lastPos] = Str::upper($username[$lastPos]);
        }
        
        // Verificar unicidad y agregar número si es necesario
        $originalUsername = $username;
        $counter = 1;
        
        while (static::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        return $username;
    }

}
