## História de Usuário: Cadastro Tipo de Responsável

**Como** Administrador do Sistema 
**Quero** cadastrar, editar e excluir tipos de situacao (Ex.: 'Cadastrada', 'Avaliada', 'Encaminhada', etc.) 
**Para** para que o tipo situação seja devidamente classificado e organizado nas fichas.

---

### Critérios de Aceitação

- ✅ O sistema deve permitir o preenchimento do nome do tipo de situação (obrigatório).
- ✅ O sistema deve bloquear o preenchimento em branco.
- ✅ O formulário deve exibir mensagens de erro caso campos obrigatórios estejam vazios ou inválidos. 
- ✅ O tipo de responsável deve ser salvo corretamente no banco de dados com todos os dados informados.  
- ✅ Ao cadastrar com sucesso, o sistema deve redirecionar para a listagem com uma mensagem de confirmação.
- ✅ Deve haver a possibilidade de cancelar o cadastro e retornar à listagem.
- ✅ O layout do formulário deve ser responsivo e seguir os padrões visuais do sistema (Tailwind CSS).  
- ✅ A exclusão de um tipo de situação só deve ser permitida se ele não estiver associado a nenhuma ficha. 

---

### Campos do Formulário

| Campo              | Tipo       | Obrigatório | Regras                                                   |
|--------------------|------------|-------------|----------------------------------------------------------|
| Nome Tipo Situacao | Texto      | Sim         | Máximo de 10 caracteres                                  |
|Descrição da Situação| Texto     | Não         | Máximo de 255 caracteres                                 |

---

### Mensagens Esperadas

- 💬 *"Nome da Situação é obrigatório"* (após sucesso)
- 💬 *"Não é possível excluir: este tipo de situação está vinculado a fichas"* (erro de restrição)