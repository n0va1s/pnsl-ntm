<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Equipe;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EquipePolicy
{
    /**
     * coord-geral tem acesso global: intercepta TODAS as habilidades antes do método específico.
     * Retorna null para não-coord-geral — a decisão flui para o método específico.
     * RBAC-10: coord-geral coexiste com users.role sem remover permissões legadas.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isCoordenadorGeral()) {
            return true;
        }

        return null;
    }

    /**
     * Listar equipes: qualquer usuário autenticado pode ver a lista.
     * Filtragem por movimento (Equipe::paraMovimento) é responsabilidade do scope, não da policy.
     * RBAC-07.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Ver equipe individual: qualquer membro ativo (inclui coordenadores de equipe) pode ver.
     * isMembroDe() cobre TODOS os papéis — não só PapelEquipe::MembroEquipe.
     * RBAC-07.
     */
    public function view(User $user, Equipe $equipe): bool
    {
        return $user->isMembroDe($equipe)
            || $user->isCoordenadorDe($equipe);
    }

    /**
     * Criar equipe: exclusivo de coord-geral, autorizado pelo before().
     * Este método explicita a habilidade usada nas rotas/FormRequests de Phase 3.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Editar equipe: coord-geral já interceptado em before().
     * Método só é alcançado por não-coord-geral — coord-equipe-h/m da própria equipe.
     * Retorna Response para mensagem pt_BR na resposta 403. RBAC-07.
     */
    public function update(User $user, Equipe $equipe): Response
    {
        return $user->isCoordenadorDe($equipe)
            ? Response::allow()
            : Response::deny('Apenas coordenadores da equipe podem editá-la.');
    }

    /**
     * Atribuir membros: exclusivo de coord-geral.
     * Mantido explícito para rotas/componentes, documentação e testes unitários.
     * RBAC-07, ATRIB-01.
     */
    public function assignMembers(User $user, Equipe $equipe): bool
    {
        return $user->isCoordenadorGeral();
    }
}
