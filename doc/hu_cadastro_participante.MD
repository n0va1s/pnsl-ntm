
### História de Usuário: Cadastro de Participante

**Como** administrador do sistema
**Quero** cadastrar, editar, listar, ativar/inativar e excluir os itens do domínio "[Nome do Domínio]"
**Para** que esses valores possam ser reutilizados em outros cadastros do sistema de forma padronizada

🧩 Critérios de Aceite:
✅ Deve existir uma tabela no banco de dados com os seguintes campos: id, nome, descricao, ativo, created_at, updated_at

✅ Deve ser possível:

Criar um novo item no domínio

Listar todos os itens cadastrados

Editar um item existente

Ativar/Inativar um item (soft delete ou flag ativo)

Excluir permanentemente um item (se necessário)

✅ O campo nome deve ser único e obrigatório

✅ Deve haver validação para evitar nomes duplicados

✅ Os itens ativos devem ser exibidos em campos de seleção em outros formulários do sistema

✅ O front-end deve ter uma interface amigável com formulário e tabela

✅ Apenas usuários com permissão adequada podem gerenciar o domínio

🔄 Reutilização:
Este padrão pode ser reutilizado para qualquer domínio simples, como:

Tipo de responsável

Tipo de contato

Categoria de produto

Tipo de evento

Status de solicitação

🗂️ Observações técnicas:
Backend: deve expor uma API RESTful para CRUD

Frontend: interface amigável (formulário + listagem com filtros)

Integração: esse domínio será referenciado por outras entidades (ex: campo de seleção em formulários)