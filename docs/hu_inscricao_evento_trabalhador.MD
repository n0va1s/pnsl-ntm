## História de Usuário: Inscrição para Evento (Trabalhador)

**Como** Pessoa  
**Quero** me increver para trabalhar em novos eventos no sistema,  
**Para** poder me voluntariar em alguma equipe para que o encontro aconteça

---

### Critérios de Aceitação

- ✅ O sistema deve bloquear o preenchimento do nome completo.
- ✅ O sistema deve bloquear o preenchimento do número de telefone.
- ✅ O sistema deve permitir a escolha de no máximo 3 equipes de interesse.
- ✅ As equipes de interesse pode ser apenas 1 ou 2.
- ✅ O campo Habilidades deve ser um campo para livre preenchimento.
- ✅ O campo "Primeira vez trabalhando" deve ter o campo por padrão como "Não".
- ✅ Deve ser exibido uma mensagem de Sucesso após o trabalhador se inscrever.
- ✅ Os dados do interessado deve ser guardado no banco de dados dos candidatos a trabalhar no evento.
- ✅ Deve haver a possibilidade de cancelar a inscrição e retornar à listagem de eventos.
- ✅ O layout do formulário deve ser responsivo e seguir os padrões visuais do sistema (Tailwind CSS).

---

### Campos do Formulário

| Campo              | Tipo       | Obrigatório | Regras                                                   |
|--------------------|------------|-------------|-----------------------------------------------------------|
| Nome Completo      | Texto      | Sim         | Máximo de 50 caracteres                                  |
| Telefone           | Numero     | Sim         | Até 11 caracteres                                          |
| Equipe de Interesse|Seleção Multipla        | Não         | Pode marcar no máximo 3 equipes de interesse |
| Habilidades        | Texto      | Não         | eMáximo de 255 caracteres                                     |
| Primeira vez Trabalhando?       | Checkbox   | Não         | Valor booleano (sim/não)                      |

---

### Mensagens Esperadas

- 💬 *"Inscição feita com sucesso, fique de olho no celular, vamos entrar em contato por ligação ou por mensagem"* (após sucesso)

