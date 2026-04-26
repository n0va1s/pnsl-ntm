<?php

namespace App\Models;

use App\Enums\PapelEquipe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model pivot para equipe_usuario.
 *
 * DECISAO D-01: Extende Model (NAO Pivot) pois SoftDeletes nao e suportado
 * em Pivot no Laravel 12. Usa AsPivot trait para manter compatibilidade com
 * using() em belongsToMany (fornece fromRawAttributes, setPivotKeys etc.)
 * Ref: https://laravel.com/docs/12.x/eloquent-relationships#defining-custom-intermediate-table-models
 *
 * AUDITORIA D-02: Usa padrao usr_* + dat_* (novo padrao). Ficha usa usu_* (legado — nao alterar).
 */
class EquipeUsuario extends Model
{
    use AsPivot, HasFactory, SoftDeletes;

    protected $table = 'equipe_usuario';

    protected $primaryKey = 'idt_equipe_usuario';

    // Obrigatorio ao usar AsPivot com id customizado
    public $incrementing = true;

    // Usa dat_inclusao/dat_alteracao manuais via booted(); nao usa created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'idt_equipe',
        'user_id',
        'papel',
        'usr_inclusao',
        'usr_alteracao',
        'dat_inclusao',
        'dat_alteracao',
    ];

    protected $casts = [
        'papel' => PapelEquipe::class,
        'dat_inclusao' => 'datetime',
        'dat_alteracao' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (auth()->check()) {
                $model->usr_inclusao = $model->usr_inclusao ?? auth()->id();
            }
            $model->dat_inclusao = $model->dat_inclusao ?? now();
        });

        static::updating(function (self $model): void {
            if (auth()->check() && $model->isDirty() && ! $model->isDirty('usr_alteracao')) {
                $model->usr_alteracao = auth()->id();
                $model->dat_alteracao = now();
            }
        });

        static::deleting(function (self $model): void {
            if (auth()->check()) {
                $model->usr_alteracao = auth()->id();
                $model->dat_alteracao = now();
                $model->saveQuietly();
            }
        });
    }

    // Relations

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'idt_equipe', 'idt_equipe');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usr_inclusao');
    }

    public function alterador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usr_alteracao');
    }
}
