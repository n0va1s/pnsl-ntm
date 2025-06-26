### História de Usuário: Cadastro de Ficha do VEM  
**Como** coordenação geral  
**Quero** cadastrar, editar e excluir fichas de candidatos ao VEM  
**Para que** possamos organizar e acompanhar as inscrições, informações pessoais e restrições dos participantes  

---

### Critérios de Aceitação  
✅ O sistema deve permitir o preenchimento dos dados pessoais do candidato, incluindo nome, apelido, data de nascimento, contato e endereço.  
✅ O sistema deve permitir a escolha do evento ao qual o candidato está se inscrevendo.  
✅ O sistema deve permitir informar os responsáveis (pai, mãe, ou outro) e seus respectivos contatos.  
✅ O sistema deve permitir indicar se o candidato possui restrição alimentar e, se sim, listar e detalhar as restrições.  
✅ O sistema deve validar campos obrigatórios com mensagens claras.  
✅ Ao salvar, os dados devem ser persistidos corretamente nas tabelas relacionadas (`ficha`, `ficha_vem`, `ficha_saude`, etc).  
✅ O sistema deve redirecionar para a listagem após o cadastro com uma mensagem de sucesso.  
✅ O layout deve ser responsivo e aderente ao padrão visual do sistema (Tailwind CSS).  
✅ O formulário deve ser bloqueado para edição se a ficha estiver com `ind_aprovado = true`.  
✅ Deve ser possível visualizar os dados mesmo quando bloqueados.  
✅ As opções de restrição alimentar devem aparecer quando selecionada a opção Possui restrição alimentar
✅ Caso a opção de restrição alimentar seja desmarcada deve garantir que as informações anteriores sejam apagadas

---

### Campos do Formulário  

| Campo                     | Tipo     | Obrigatório | Regras / Observações                            |
|---------------------------|----------|-------------|--------------------------------------------------|
| Evento                    | Select   | Sim         | Deve listar apenas eventos do tipo VEM          |
| Nome Completo             | Texto    | Sim         | Máximo de 255 caracteres                         |
| Apelido                   | Texto    | Sim         | Máximo de 100 caracteres                         |
| Data de Nascimento        | Data     | Sim         | Formato: AAAA-MM-DD                              |
| Gênero                    | Select   | Sim         | Opções: M, F, Outro                              |
| Telefone                  | Texto    | Não         | Máscara: (00) 00000-0000                         |
| Email                     | Email    | Sim         | Deve ser válido                                  |
| Endereço                  | Texto    | Não         | Até 500 caracteres                               |
| Tamanho da Camiseta       | Select   | Sim         | Opções: PP, P, M, G, GG                          |
| Como soube do evento      | Select   | Não         | Ex: Indicação, Padre, Outro                      |
| É católico?               | Checkbox | Não         | Valor binário                                    |
| Toca instrumento?         | Checkbox | Não         | Valor binário                                    |
| Nome do Pai               | Texto    | Não         | Campo usado em ficha_vem                         |
| Telefone do Pai           | Texto    | Não         |                                                  |
| Nome da Mãe               | Texto    | Não         |                                                  |
| Telefone da Mãe           | Texto    | Não         |                                                  |
| Onde estuda               | Texto    | Não         |                                                  |
| Com quem mora             | Texto    | Não         |                                                  |
| Falar com (responsável)   | Select   | Não         | Lista de responsáveis pré-cadastrados            |
| Possui restrição alimentar? | Checkbox | Não       | Exibe campos de restrição se marcado             |
| Restrições alimentares    | Lista + Texto | Condicional | Seleção múltipla com complemento opcional       |

---

### Mensagens Esperadas  
💬 "Ficha cadastrada com sucesso!" (após sucesso)  
💬 "O campo nome completo é obrigatório." (erro de validação)  
💬 "A data de nascimento é obrigatória." (erro de validação)  
💬 "O campo email deve conter um endereço válido." (erro de validação)  
💬 "Ficha atualizada com sucesso!" (edição concluída)  
💬 "Não é possível editar: a ficha já foi aprovada." (bloqueio por regra de negócio)  
