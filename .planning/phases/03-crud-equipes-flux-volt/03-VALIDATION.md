# Phase 3 Validation — CRUD de equipes Flux/Volt

**Criado:** 2026-04-24
**Escopo:** Phase 3 (`03-01` a `03-04`)
**Status:** pronto para recheck antes da execução

## Decisao de escopo

EQUIPE-08 foi movido formalmente para Phase 4 porque a regra H+M depende da escrita na pivot `equipe_usuario` durante atribuicao/troca de papel. Phase 3 valida apenas o CRUD da entidade `Equipe`: FormRequests, rotas, Volt SFCs, autorizacao, soft-delete e restauracao.

## Matriz de Validacao

| Plan | Must-have | Verificacao planejada | Evidencia esperada |
|------|-----------|-----------------------|--------------------|
| 03-01 | `EquipeStoreRequest::rules()` exige `nom_equipe` required/max:60, `des_slug` nullable/unique por `idt_movimento`, `des_descricao` nullable/max:500 | Sintaxe PHP + inspeção automatizada + testes de CRUD em `EquipeCrudTest.php` | Arquivo existe com `Rule::unique('equipes', 'des_slug')->where('idt_movimento', $this->idtMovimentoUsuario())` |
| 03-01 | `EquipeUpdateRequest::rules()` valida os mesmos campos e inclui `ind_ativa` boolean | Sintaxe PHP + inspeção automatizada + `EquipeCrudTest.php` edit/toggle | Arquivo existe com `ind_ativa` boolean |
| 03-01 | `EquipeUpdateRequest` ignora o registro atual pela PK customizada `idt_equipe` | Inspeção automatizada + teste de editar sem erro mantendo slug atual | `->ignore($equipe->idt_equipe, 'idt_equipe')` presente |
| 03-01 | Ambos os FormRequests têm messages em pt_BR | Inspeção dos arquivos + Pint | `messages()` contém mensagens para `nom_equipe`, `des_slug`, `des_descricao` e `ind_ativa` |
| 03-01 | `authorize()` delega para Gate (`create` e `update`) | Inspeção dos arquivos + testes 403/200 de rotas/SFC | `can('create', Equipe::class)` e `can('update', $this->route('equipe'))` presentes |
| 03-02 | `GET /equipes` retorna 200 para coord-geral e 403 para usuário sem permissão de CRUD | `EquipeCrudTest.php` | Testes HTTP de listagem e bloqueio passam |
| 03-02 | Index usa `Equipe::paraMovimento($this->idtMovimentoUsuario())` | Inspeção do SFC + teste com equipes de movimentos distintos | Listagem exibe apenas equipes do movimento derivado do vínculo ativo do usuário em `equipe_usuario` |
| 03-02 | `arquivar(idt_equipe)` aplica soft-delete e preserva pivot | `EquipeArquivamentoTest.php` | `Equipe::find()` retorna null, `withTrashed()` encontra registro, `equipe_usuario` segue com linhas |
| 03-02 | `restaurar(idt_equipe)` limpa `deleted_at` | `EquipeArquivamentoTest.php` | Após restaurar, `Equipe::find($idt)` retorna modelo ativo |
| 03-02 | Rotas `equipes.index`, `equipes.create`, `equipes.edit` registradas no grupo `auth` com `->can()` em escrita | Inspeção de `routes/web.php` + testes HTTP | Rotas existem e rotas de create/edit bloqueiam usuário sem coord-geral |
| 03-03 | Coord-geral cria equipe com `nom_equipe` e `des_descricao`; `des_slug` auto-gerado se omitido | `EquipeCrudTest.php` via `Volt::test('equipes.create')` | Registro persiste com slug gerado pelo mutator |
| 03-03 | Create redireciona para `equipes.index` após salvar | `EquipeCrudTest.php` | `assertRedirect(route('equipes.index'))` ou equivalente |
| 03-03 | Edit carrega `nom_equipe`, `des_slug`, `des_descricao` e `ind_ativa` no `mount()` | `EquipeCrudTest.php` via `Volt::test('equipes.edit')` | Propriedades iniciais batem com o model |
| 03-03 | Edit salva alterações e toggle de `ind_ativa` | `EquipeCrudTest.php` | Banco reflete valores alterados |
| 03-03 | Slug duplicado em create/edit retorna erro de validação | `EquipeCrudTest.php` | `assertHasErrors(['des_slug'])` no mesmo movimento |
| 03-03 | Usuário sem coord-geral recebe 403 em create/edit | `EquipeCrudTest.php` | HTTP 403 nas rotas ou falha de autorização Livewire |
| 03-04 | `EquipeCrudTest.php` cobre listagem, criação, edição, validação e autorização | Execução Pest focada | `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/EquipeCrudTest.php` verde |
| 03-04 | `EquipeArquivamentoTest.php` cobre soft-delete, preservação da pivot e restauração | Execução Pest focada | `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/EquipeArquivamentoTest.php` verde |
| 03-04 | Suite de equipes passa como gate de Phase 3 | Execução Pest da pasta | `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/` verde |

## Comandos de Gate

```bash
C:/xampp/php/php.exe -l app/Http/Requests/EquipeStoreRequest.php
C:/xampp/php/php.exe -l app/Http/Requests/EquipeUpdateRequest.php
C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/
./vendor/bin/pint app/Http/Requests/EquipeStoreRequest.php app/Http/Requests/EquipeUpdateRequest.php tests/Feature/Equipes/EquipeCrudTest.php tests/Feature/Equipes/EquipeArquivamentoTest.php --test
```

## Resultado esperado do recheck

---

## Audit GSD manual - 2026-04-24

**Skill aplicada:** `gsd-validate-phase`
**Modo:** manual, porque o `gsd-sdk` local falha com modulo ausente `@gsd-build/sdk/dist/cli.js`.
**Estado detectado:** A - `03-VALIDATION.md` existe; auditoria retroativa e preenchimento de evidencias.
**Resultado:** PASS.

### Evidencias finais

- `C:/xampp/php/php.exe artisan route:list --name=equipes` PASS: rotas `equipes.index`, `equipes.create`, `equipes.edit` registradas.
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature/Equipes/` PASS: 23 testes, cobrindo migrations/model/policies/CRUD/arquivamento de equipes.
- `C:/xampp/php/php.exe vendor/bin/pest tests/Unit` PASS: 60 testes, 141 assertions.
- `C:/xampp/php/php.exe vendor/bin/pest tests/Feature` PASS: 251 testes, 611 assertions.
- `C:/xampp/php/php.exe vendor/bin/pest` PASS: 311 testes, 752 assertions.

### Cobertura por plan

- `03-01` PASS: `EquipeStoreRequest` e `EquipeUpdateRequest` existem, delegam `authorize()` ao Gate, validam slug unico por movimento e update ignora `idt_equipe`.
- `03-02` PASS: rotas e index Volt existem; index filtra por movimento derivado da pivot `equipe_usuario`; arquivar/restaurar preserva pivot.
- `03-03` PASS: create/edit Volt persistem criacao, edicao, slug manual/auto e toggle `ind_ativa`.
- `03-04` PASS: `EquipeCrudTest.php` e `EquipeArquivamentoTest.php` cobrem os must-haves executaveis da Phase 3.

### Decisoes confirmadas

- EQUIPE-08 permanece fora da Phase 3 e foi diferido para Phase 4, porque depende da escrita de papeis na pivot `equipe_usuario`.
- Nenhum gap de validacao restante bloqueia a Phase 3.

- Nenhum must-have da Phase 3 depende de EQUIPE-08.
- EQUIPE-08 aparece somente como decisao diferida para Phase 4.
- Todos os must-haves executaveis da Phase 3 possuem teste, inspeção automatizada ou ambos.
