<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Mail\BoasVindasMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_USER = 'user';

    const ROLE_ADMIN = 'admin';

    const ROLE_COORDENADOR = 'coord';

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCoordenador(): bool
    {
        return $this->role === self::ROLE_COORDENADOR;
    }

    public function pessoa()
    {
        return $this->hasOne(Pessoa::class, 'idt_usuario', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (User $user) {
            $user->pessoa()->create([
                'idt_usuario' => $user->id,
                'nom_pessoa' => $user->name,
                'eml_pessoa' => $user->email,
                'dat_nascimento' => '1900-01-01',
            ]);

            try {
                Mail::to($user->email)->send(new BoasVindasMail($user, $user->pessoa->dat_nascimento->format('Ymd')));
            } catch (\Exception $e) {
                Log::error('Falha ao enviar e-mail de boas-vindas para '.$user->email.': '.$e->getMessage());
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
