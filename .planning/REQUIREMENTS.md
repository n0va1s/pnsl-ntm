# Requirements: pnsl-ntm — Marco v1.1 Gestão de Equipes VEM (Fundação)

**Defined:** 2026-04-21
**Core Value:** Permitir que a coordenação paroquial estruture as 11 equipes VEM com papéis escopados (coord-geral, coord-equipe-h/m, membro-equipe) e atribua pessoas a elas, estabelecendo a fundação de RBAC escopado sobre a qual os marcos v1.2+ (Espaços, Score 0-100, Vendinha, IA) serão construídos.

## v1 Requirements

Requisitos commitados para o marco v1.1. Cada um mapeia para uma fase do roadmap (preenchido após `gsd-roadmapper`).

### RBAC escopado (baseline Gate/Policy + pivot)

- [ ] **RBAC-01**: Constants/enum dos papéis de equipe (`coord-geral`, `coord-equipe-h`, `coord-equipe-m`, `membro-equipe`) em classe dedicada
- [ ] **RBAC-02**: Migration `equipe_usuario` com FKs (`user_id`, `equipe_id`), coluna `papel`, colunas de auditoria (`usr_inclusao`, `dat_inclusao`, `usr_alteracao`, `dat_alteracao`), soft deletes
- [ ] **RBAC-03**: Unique constraint `(user_id, equipe_id)` para impedir duplo vínculo de uma pessoa à mesma equipe
- [ ] **RBAC-04**: Model Pivot `EquipeUsuario` com cast do campo `papel` para o enum de RBAC-01
- [ ] **RBAC-05**: Relação `User::equipes()` `belongsToMany` via pivot com `withPivot('papel')` e `withTimestamps()`
- [ ] **RBAC-06**: Relação inversa `Equipe::usuarios()`, escopos auxiliares `coordenadores()` e `membros()` usando o campo `papel`
- [ ] **RBAC-07**: `EquipePolicy` nativa do Laravel (`viewAny`, `view`, `update`, `assignMembers`) com regras baseadas no papel na pivot
- [ ] **RBAC-08**: Helpers no model `User`: `isCoordenadorGeral()`, `isCoordenadorDe(Equipe $e)`, `isMembroDe(Equipe $e)`
- [ ] **RBAC-09**: Registro da Policy em `AuthServiceProvider::$policies`
- [ ] **RBAC-10**: `coord-geral` coexiste com `users.role ∈ {admin, coord}` sem remover permissões legadas

### Estrutura das 11 equipes VEM

- [ ] **EQUIPE-01**: Migration `equipes` (id, nome, slug, `idt_movimento` FK, descricao, ativo bool, timestamps, soft deletes)
- [ ] **EQUIPE-02**: Model `Equipe` com scopes `paraMovimento(idt)` e `ativas()`, mutator de `slug`
- [ ] **EQUIPE-03**: Seeder com as 11 equipes VEM: sala, limpeza, reportagem, oração, vendinha, alimentação, emaús, secretaria, troca de ideias, recepção, bandinha
- [ ] **EQUIPE-04**: `FormRequest` `EquipeStoreRequest` + `EquipeUpdateRequest` validando `nome` (required, max:60), `slug` (unique:equipes), `descricao` (nullable, max:500)
- [ ] **EQUIPE-05**: Volt component `equipes.index` — lista equipes filtradas por movimento do usuário logado
- [ ] **EQUIPE-06**: Volt component `equipes.create` — formulário de criação restrito a `coord-geral`
- [ ] **EQUIPE-07**: Volt component `equipes.edit` — edição + toggle ativar/desativar, restrito a `coord-geral`
- [ ] **EQUIPE-08**: Regra de validação: cada equipe aceita **no máximo** 1 `coord-equipe-h` e 1 `coord-equipe-m` simultaneamente (enforce via `FormRequest` + teste)
- [ ] **EQUIPE-09**: Rotas `equipes.*` com middleware `auth` e autorização via `EquipePolicy`
- [ ] **EQUIPE-10**: Arquivamento via soft-delete preservando histórico da pivot e permitindo restauração

### Atribuição de membros e coordenadores

- [ ] **ATRIB-01**: Volt component `equipes.atribuir` acessível apenas por `coord-geral` (guard via Gate)
- [ ] **ATRIB-02**: Listagem de pessoas elegíveis filtrada por `idt_movimento = VEM`; quando o slot for coord-equipe-h/m, filtra também por sexo da `pessoa` vinculada
- [ ] **ATRIB-03**: Ação "Atribuir à equipe" cria registro em `equipe_usuario` com papel escolhido
- [ ] **ATRIB-04**: Ação "Remover da equipe" executa soft-delete na pivot preservando histórico
- [ ] **ATRIB-05**: Ação "Alterar papel" atualiza a coluna `papel` na pivot (ex.: membro → coord-equipe-h)
- [ ] **ATRIB-06**: Validação runtime: bloquear 2º `coord-equipe-h` ou 2º `coord-equipe-m` na mesma equipe com mensagem clara
- [ ] **ATRIB-07**: Perfil do usuário (`Pessoa`) exibe lista das equipes às quais pertence com o papel respectivo
- [ ] **ATRIB-08**: Log de alterações preenchendo `usr_alteracao` + `dat_alteracao` na pivot em toda operação de write

### Migração e preservação de invariantes

- [ ] **MIG-01**: `users.role` flat permanece intacto — nenhuma migration altera o schema atual da coluna
- [ ] **MIG-02**: `OnlyManagerMiddleware` (alias `manager`) continua guardando `configuracoes.*` para `role ∈ {admin, coord}`
- [ ] **MIG-03**: Permissões novas via Gate/Policy coexistem com o middleware legado em rotas pré-existentes
- [ ] **MIG-04**: Migrations reversíveis — `down()` remove pivot e `equipes` em ordem correta, sem orfanar FKs
- [ ] **MIG-05**: Cascade `User ↔ Pessoa` via `saveQuietly()` preservada; `BoasVindasMail` continua disparando com senha DDMMYYYY
- [ ] **MIG-06**: `GamificacaoObserver` continua registrado em `AppServiceProvider::boot()` e atualizando `pessoa.qtd_pontos_total`
- [ ] **MIG-07**: Suite legada (~24 Feature + 3 Unit) continua verde após refactor — zero regressões

### Testes e qualidade

- [ ] **TEST-01**: Pest coverage ≥80% em todo código novo de `app/` introduzido no marco v1.1
- [ ] **TEST-02**: Feature tests da UI de atribuição — `coord-geral` autorizado, `membro-equipe`/`user` bloqueado (403)
- [ ] **TEST-03**: Unit tests da `EquipePolicy` cobrindo todas as habilidades (viewAny, view, update, assignMembers)
- [ ] **TEST-04**: Feature test da restrição H+M — tentar cadastrar 2º coord-equipe-h retorna erro de validação
- [ ] **TEST-05**: Teste de migration `up`/`down` reversível em SQLite (dev/CI) **e** MySQL (prod-like)
- [ ] **TEST-06**: Teste do seed das 11 equipes (contagem exata + nomes esperados + `idt_movimento = VEM`)
- [ ] **TEST-07**: Smoke test de regressão: `OnlyManagerMiddleware` continua bloqueando `configuracoes.*` para `role = user` e liberando para `role = admin`
- [ ] **TEST-08**: `vendor/bin/pint --test` passa sem diff no CI

## v2 Requirements

Diferidos para marcos futuros — reconhecidos mas fora do roadmap atual.

### Espaços de equipe (v1.2)

- **ESPACO-01**: Quadro de comunicados/instruções por equipe
- **ESPACO-02**: Eventos internos da equipe (reuniões, ensaios, tarefas)
- **ESPACO-03**: Confirmação de presença em eventos internos
- **ESPACO-04**: Histórico de comunicados e eventos visível para membros da equipe

### Gamificação Score 0-100 (v1.3)

- **SCORE-01**: Rubrica thinkworklab com faixas qualitativas (0-100)
- **SCORE-02**: Componente de frequência derivado das presenças
- **SCORE-03**: Componente de avaliação preenchido pelo coordenador da equipe
- **SCORE-04**: Aplicação para todos os usuários (inclusive coordenadores)
- **SCORE-05**: Visibilidade dos scores restrita à coordenação geral

### Módulo Vendinha (v1.4)

- **VEND-01**: Registro de vendas (produto, quantidade, valor, data, responsável)
- **VEND-02**: Estatísticas agregadas: receita, volume, ticket médio
- **VEND-03**: Ranking de produtos mais vendidos
- **VEND-04**: Dashboard de histórico por período

### Análise de IA para Vendinha (v1.5)

- **AI-01**: ROI por produto com recomendações
- **AI-02**: Detecção de sazonalidade nas vendas
- **AI-03**: Sugestões de catálogo baseadas em histórico

## Out of Scope

Exclusões explícitas para evitar escopo inflado no marco v1.1.

| Feature | Reason |
|---------|--------|
| Reescrever `users.role` flat para enum/tabela de roles | Marco v1.1 é aditivo: pivot convive com role legado, reduzindo risco e mantendo `OnlyManagerMiddleware` funcional |
| Extensão do RBAC escopado para movimentos ECC/SGM | Marco é VEM-only; ECC/SGM permanecem com autorização flat até marco específico |
| UI de coord-equipe para gerenciar própria equipe | Apenas `coord-geral` gerencia atribuições no v1.1; gestão delegada fica para v1.2 |
| Comunicados, eventos internos e confirmação de presença por equipe | Pertencem ao marco v1.2 (Espaços de equipe) |
| Gamificação Score 0-100 com avaliação do coordenador | Pertence ao marco v1.3; `GamificacaoObserver` atual continua como está |
| Módulo Vendinha (vendas, estatísticas, ROI) | Pertence aos marcos v1.4/v1.5; `equipe-vendinha` é apenas uma row no seed v1.1 |
| Multi-paróquia/multi-tenancy | Fora do escopo global do produto (ver PROJECT.md → Out of Scope) |
| Broadcasting/realtime para mudanças em equipes | Sem `routes/channels.php`; PWA autoUpdate cobre o caso |
| API REST/GraphQL pública para integração externa | Sistema UI-only Livewire; `routes/api.php` não existe |
| Integração com sistema externo de presença | Presença continua via `Presenca` model interno |

## Traceability

Mapeamento requisito → fase. Preenchido por `gsd-roadmapper` em 2026-04-21.

| Requirement | Phase | Status |
|-------------|-------|--------|
| RBAC-01 | Phase 1 | Pending |
| RBAC-02 | Phase 1 | Pending |
| RBAC-03 | Phase 1 | Pending |
| RBAC-04 | Phase 1 | Pending |
| RBAC-05 | Phase 1 | Pending |
| RBAC-06 | Phase 1 | Pending |
| RBAC-07 | Phase 2 | Pending |
| RBAC-08 | Phase 2 | Pending |
| RBAC-09 | Phase 2 | Pending |
| RBAC-10 | Phase 2 | Pending |
| EQUIPE-01 | Phase 1 | Pending |
| EQUIPE-02 | Phase 1 | Pending |
| EQUIPE-03 | Phase 1 | Pending |
| EQUIPE-04 | Phase 3 | Pending |
| EQUIPE-05 | Phase 3 | Pending |
| EQUIPE-06 | Phase 3 | Pending |
| EQUIPE-07 | Phase 3 | Pending |
| EQUIPE-08 | Phase 3 | Pending |
| EQUIPE-09 | Phase 3 | Pending |
| EQUIPE-10 | Phase 3 | Pending |
| ATRIB-01 | Phase 4 | Pending |
| ATRIB-02 | Phase 4 | Pending |
| ATRIB-03 | Phase 4 | Pending |
| ATRIB-04 | Phase 4 | Pending |
| ATRIB-05 | Phase 4 | Pending |
| ATRIB-06 | Phase 4 | Pending |
| ATRIB-07 | Phase 4 | Pending |
| ATRIB-08 | Phase 4 | Pending |
| MIG-01 | Phase 2 | Pending |
| MIG-02 | Phase 2 | Pending |
| MIG-03 | Phase 2 | Pending |
| MIG-04 | Phase 1 | Pending |
| MIG-05 | Phase 5 | Pending |
| MIG-06 | Phase 5 | Pending |
| MIG-07 | Phase 5 | Pending |
| TEST-01 | Phase 5 | Pending |
| TEST-02 | Phase 2 | Pending |
| TEST-03 | Phase 2 | Pending |
| TEST-04 | Phase 4 | Pending |
| TEST-05 | Phase 1 | Pending |
| TEST-06 | Phase 1 | Pending |
| TEST-07 | Phase 2 | Pending |
| TEST-08 | Phase 5 | Pending |

**Coverage:**
- v1 requirements: 43 total (10 RBAC + 10 EQUIPE + 8 ATRIB + 7 MIG + 8 TEST)
- Mapped to phases: 43 ✓
- Unmapped: 0 ✓

---
*Requirements defined: 2026-04-21*
*Last updated: 2026-04-21 após geração do roadmap com traceability completa (5 fases, 43/43 requisitos mapeados)*
