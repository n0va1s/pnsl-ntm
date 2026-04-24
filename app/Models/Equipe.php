<?php

namespace App\Models;

use App\Enums\PapelEquipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Equipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipes';

    protected $primaryKey = 'idt_equipe';

    public $timestamps = true;

    protected $fillable = [
        'idt_movimento',
        'nom_equipe',
        'des_slug',
        'des_descricao',
        'ind_ativa',
    ];

    protected $casts = [
        'ind_ativa' => 'boolean',
    ];

    // Mutator: gera slug automaticamente via Str::slug ao definir o nome
    // Trata acentos pt_BR: Emaús→emaus, Oração→oracao, Troca de Ideias→troca-de-ideias
    protected function setNomEquipeAttribute(string $value): void
    {
        $this->attributes['nom_equipe'] = $value;
        if (empty($this->attributes['des_slug'])) {
            $this->attributes['des_slug'] = Str::slug($value);
        }
    }

    // Scopes

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('ind_ativa', true);
    }

    public function scopeParaMovimento(Builder $query, ?int $idtMovimento): Builder
    {
        return $idtMovimento ? $query->where('idt_movimento', $idtMovimento) : $query;
    }

    // Relations

    public function movimento(): BelongsTo
    {
        return $this->belongsTo(TipoMovimento::class, 'idt_movimento', 'idt_movimento');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'equipe_usuario', 'idt_equipe', 'user_id')
            ->using(EquipeUsuario::class)
            ->withPivot(['papel', 'usr_inclusao', 'usr_alteracao', 'dat_inclusao', 'dat_alteracao'])
            ->whereNull('equipe_usuario.deleted_at');
    }

    /**
     * Retorna todos os coordenadores da equipe (papeis diferentes de membro_equipe)
     * Usa o valor string do enum para evitar cast mismatch no where (R-07)
     */
    public function coordenadores(): BelongsToMany
    {
        return $this->usuarios()->wherePivotIn('papel', [
            PapelEquipe::CoordGeral->value,
            PapelEquipe::CoordEquipeH->value,
            PapelEquipe::CoordEquipeM->value,
        ]);
    }

    /**
     * Retorna somente membros da equipe (papel = membro_equipe)
     */
    public function membros(): BelongsToMany
    {
        return $this->usuarios()->wherePivot('papel', PapelEquipe::MembroEquipe->value);
    }
}
