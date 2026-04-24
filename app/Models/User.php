<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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

    /**
     * Equipes VEM às quais o usuário pertence via pivot equipe_usuario.
     * Vínculos soft-deleted são excluídos pelo whereNull no pivot.
     * D-09: withTimestamps() NÃO usado — pivot usa dat_* manual via booted().
     * RBAC-05: pivot traz papel (castado para PapelEquipe) e colunas de auditoria.
     */
    public function equipes(): BelongsToMany
    {
        return $this->belongsToMany(Equipe::class, 'equipe_usuario', 'user_id', 'idt_equipe')
            ->using(EquipeUsuario::class)
            ->withPivot(['papel', 'usr_inclusao', 'usr_alteracao', 'dat_inclusao', 'dat_alteracao'])
            ->whereNull('equipe_usuario.deleted_at');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (User $user) {
            $pessoaCadastrada = Pessoa::where('eml_pessoa', $user->email)->first();

            if (! $pessoaCadastrada) {
                $user->pessoa()->create([
                    'nom_pessoa' => $user->name,
                    'eml_pessoa' => $user->email,
                    'tel_pessoa' => $user->phone,
                    'dat_nascimento' => '1900-01-01',
                ]);
            } else {
                $pessoaCadastrada->idt_usuario = $user->id;
                // Para evitar loop infinito, salvar a pessoa sem disparar eventos
                $pessoaCadastrada->saveQuietly();
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
        'phone',
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
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
