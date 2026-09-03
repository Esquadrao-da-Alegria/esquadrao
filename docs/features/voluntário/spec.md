# Voluntários — especificação da feature

Documento de referência para manutenção da feature de voluntários. Ele registra o modelo de dados atual, os fluxos existentes, as regras de acesso e os limites que devem ser preservados em futuras alterações.

---

## Contexto de domínio

O sistema separa duas entidades:

| Entidade | Responsabilidade |
|---|---|
| **User** | Conta de acesso: autenticação, senha, verificação de e-mail, dois fatores e cargos/permissões. |
| **Voluntario** | Pessoa vinculada à ONG: dados pessoais, contato, cidade, situação institucional e histórico de convite. |

Nem todo usuário precisa representar um voluntário. Contas administrativas antigas ou de sistema podem permanecer com `users.voluntario_id` nulo.

Um voluntário pode existir antes de possuir uma conta ativa, especialmente durante o convite. Na implementação atual, o convite administrativo já cria uma conta em estado `convite_enviado`, com senha aleatória não conhecida pelo convidado; a senha definitiva é definida na conclusão do cadastro.

---

## Modelo de dados

### Tabela `voluntarios`

| Coluna | Obrigatoriedade atual | Descrição |
|---|---|---|
| `id` | obrigatória | Chave primária. |
| `nome_completo` | obrigatória | Nome civil da pessoa. |
| `nome_doutor` | opcional | Nome artístico usado nas ações da ONG. |
| `email` | obrigatório e único | Contato institucional do voluntário. Também é usado na conta vinculada. |
| `telefone` | opcional no banco, obrigatório na conclusão do convite | Telefone/WhatsApp formatado com DDD. |
| `data_nascimento` | opcional no banco, obrigatória na conclusão do convite | Data de nascimento, sem aceitar data futura. |
| `cpf` | opcional e único | Armazenado no formato `000.000.000-00`. |
| `cidade_base_id` | opcional no banco, obrigatória na conclusão do convite | FK para `cidades.id`, com `nullOnDelete`. |
| `data_entrada_ong` | opcional no banco, automática nos fluxos atuais | Data em que o cadastro é ativado na ONG. |
| `status` | obrigatória | Situação institucional, usando os estados atuais do sistema. |
| `observacoes` | opcional | Texto livre administrativo, limitado a 5.000 caracteres na conclusão do convite. |
| `created_at`, `updated_at` | automáticas | Auditoria básica. |

A foto de perfil está deliberadamente fora do escopo atual. Não adicionar coluna ou upload sem antes definir disco, armazenamento persistente, limites, tratamento da imagem e política de remoção.

### Tabela `users`

Mantém os dados de acesso:

- `voluntario_id`: FK opcional para `voluntarios.id`, com `nullOnDelete`;
- `name` e `email`: identificação da conta;
- `password`: hash gerenciado pelo cast `hashed`;
- `email_verified_at`: marca a ativação/verificação atual;
- campos do Fortify, sessão e autenticação;
- campos legados de convite e inativação mantidos por compatibilidade.

### Tabela `convites_cadastro`

Registra os convites emitidos:

- vínculo obrigatório com `voluntarios`;
- token armazenado como hash SHA-256;
- e-mail convidado;
- status, envio, uso e expiração;
- usuário administrador que criou o convite, quando disponível.

### Tabela `voluntario_afastamentos`

Registra licenças e atestados médicos dos voluntários:

| Coluna | Obrigatoriedade | Descrição |
|---|---|---|
| `id` | obrigatória | Chave primária. |
| `voluntario_id` | obrigatória | FK para `voluntarios.id` com `cascadeOnDelete`. |
| `registrado_por_id` | opcional | FK para `users.id` (administrador que registrou a licença), com `nullOnDelete`. |
| `data_inicio` | obrigatória | Data inicial do afastamento. |
| `data_fim` | obrigatória | Data final do afastamento. |
| `motivo` | obrigatória | Enum `MotivoAfastamento` (`atestado_medico`, `licenca_pessoal`, `estudos`, `outro`). |
| `observacoes` | opcional | Justificativa / texto livre sobre o afastamento ou histórico de prorrogações. |
| `status` | obrigatória | Enum `StatusAfastamento` (`ativo`, `encerrado`, `prorrogado`, `cancelado`). |
| `created_at`, `updated_at` | automáticas | Auditoria básica. |

### Cargos

Os cargos continuam pertencendo à conta de acesso. A pivot histórica `voluntario_cargo` conserva esse nome, mas sua coluna `voluntario_id` referencia `users.id`, não `voluntarios.id`.

Não migrar cargos para o model `Voluntario`: autorização é responsabilidade de `User`.

---

## Relacionamentos Eloquent

```php
User::voluntario()              // belongsTo Voluntario
User::cargos()                  // belongsToMany Cargo pela pivot voluntario_cargo
Voluntario::user()              // hasOne User
Voluntario::cidadeBase()        // belongsTo Cidade
Voluntario::convitesCadastro()  // hasMany ConviteCadastro
Voluntario::conviteCadastroAtual() // hasOne, latestOfMany
Voluntario::afastamentos()      // hasMany VoluntarioAfastamento
Voluntario::afastamentoAtivo()  // hasOne VoluntarioAfastamento (ativo no período atual)
VoluntarioAfastamento::voluntario() // belongsTo Voluntario
VoluntarioAfastamento::registradoPor() // belongsTo User
```

O model `Voluntario` expõe accessors de compatibilidade (`name`, cargos e datas de convite) e controle de licença (`esta_afastado`, `afastamento_atual`).

---

## Status atuais

Os estados preservados no código são:

| Constante | Valor | Uso |
|---|---|---|
| `User::STATUS_ATIVO` | `ativo` | Cadastro ativo. |
| `User::STATUS_CONVITE_ENVIADO` | `convite_enviado` | Pessoa convidada aguardando conclusão. |
| `User::STATUS_INATIVO` | `inativo` | Voluntário inativado. |

Os convites possuem estados próprios em `ConviteCadastro`: pendente, enviado, utilizado, expirado e cancelado.

Os afastamentos possuem estados próprios em `StatusAfastamento`: ativo, encerrado, prorrogado e cancelado.

Na listagem administrativa, esses estados são consolidados como `Pendente`, `Aceito`, `Expirado` e `Cancelado`. Convites enviados e ainda válidos são apresentados como pendentes.

Não introduzir `bloqueado` ou outros estados sem definir antes as regras de login, autorização, listagem e transição. Hoje o login valida credenciais, mas não bloqueia explicitamente a autenticação com base em `users.status`.

---

## Fluxos implementados

### Cadastro direto pelo administrador

1. O administrador informa nome, e-mail, senha e cargos.
2. O sistema cria `voluntarios` com status ativo e data de entrada atual.
3. Cria `users` vinculado pelo `voluntario_id`.
4. Associa os cargos selecionados ao usuário.

### Convite

1. O administrador informa nome e e-mail.
2. O sistema cria o voluntário em `convite_enviado`.
3. Cria uma conta vinculada com senha aleatória e cargo `voluntario`.
4. Cria `convites_cadastro` e envia a notificação conforme o driver de e-mail configurado.
5. Reenvios cancelam convites pendentes anteriores e geram um token novo.

Cada convite expira sete dias após sua criação ou reenvio.

### Conclusão pelo convidado

A rota pública `/convites/{token}` valida o convite. O convidado preenche:

- nome completo;
- nome do doutor, opcional;
- e-mail do convite, que não pode ser trocado;
- telefone/WhatsApp;
- data de nascimento;
- CPF, opcional;
- cidade base;
- observações, opcionais;
- senha e confirmação.

Na conclusão, uma transação atualiza o voluntário, define a data de entrada se ausente, ativa a conta, marca o e-mail como verificado, garante o cargo `voluntario` e marca o convite como utilizado. O usuário é direcionado ao login; não há login automático.

### Inativação

A exclusão administrativa é lógica no fluxo de serviço: o voluntário e sua conta recebem status inativo, e a conta registra `inativado_em`. O administrador não pode inativar o próprio voluntário pela tela.

### Reativação

1. A listagem administrativa oculta voluntários inativos por padrão e oferece o filtro **Apenas inativos**.
2. Um cadastro é tratado como inativo quando `voluntarios.status` ou o `users.status` da conta vinculada estiver inativo. Isso permite localizar e corrigir estados inconsistentes com segurança.
3. Somente administradores podem acionar **Reativar**. A rota também é protegida pelo middleware administrativo, independentemente da interface.
4. A reativação atualiza, na mesma transação, o voluntário e sua conta para o status ativo e limpa `users.inativado_em`.
5. Senha, cargos, cidade-base, convites e históricos de participação permanecem inalterados. Depois da reativação, a pessoa volta a aparecer nas listas operacionais e pode autenticar novamente.
6. Voluntários sem uma conta vinculada não podem ser reativados por esse fluxo; o sistema preserva o estado existente e informa o problema ao administrador.

Na aba de convidados, um convite utilizado permanece com o estado **Aceito**. Quando o voluntário correspondente estiver inativo, a interface acrescenta o indicador **Voluntário inativo**, sem alterar o histórico do convite.

### Gerenciamento de Afastamentos e Atestados

1. **Cadastro de Afastamento / Atestado**:
   - Administrador ou gestor informa data de início, data de fim, motivo (`atestado_medico`, `licenca_pessoal`, `estudos`, `outro`) e observações.
   - O sistema cria o registro com status `ativo`.
   - Automaticamente cancela todas as inscrições agendadas do voluntário (`visitas_participantes`) em visitas com `inicio_em` compreendido dentro do período de afastamento.
2. **Bloqueio de Novas Inscrições**:
   - Durante o período de afastamento (`$voluntario->estaAfastado($visita->inicio_em)`), qualquer tentativa de auto-inscrição do voluntário ou adição manual por gestor é bloqueada com mensagem clara explicativa.
3. **Prorrogação de Afastamento**:
   - Administrador pode prorrogar a licença informando nova data de fim (maior que a atual) e justificativa.
   - O status é atualizado para `prorrogado`, as observações registram o histórico com timestamp e as visitas agendadas no novo período estendido são canceladas.
4. **Edição e Ajuste de Afastamento**:
   - Administrador pode alterar a qualquer momento a `data_inicio`, `data_fim`, `motivo` e `observacoes` de um afastamento ativo ou histórico.
   - Permite correções e reduções de prazo (ex: de 18/09/2026 para 01/09/2026), com validação de sobreposição exclusiva para outros afastamentos do voluntário e novo cancelamento de visitas agendadas no período atualizado.
5. **Encerramento Antecipado**:
   - Administrador pode encerrar o afastamento a qualquer momento.
   - O status é atualizado para `encerrado`, a `data_fim` é definida como a data atual e o voluntário volta a poder participar de novas visitas normalmente.
6. **Exclusão de Afastamento**:
   - Administrador pode excluir um registro de afastamento lançado indevidamente ou por engano.

---

## Autenticação e autorização

- Login, logout, senha e dois fatores permanecem sob Laravel Fortify e o model `User`.
- `/dashboard` exige apenas `auth` e `verified`, portanto usuários comuns autenticados podem acessá-lo.
- `/voluntarios`, `/hospitais` e `/patrocinadores` exigem o middleware `administrador`.
- A reativação em `PATCH /voluntarios/{voluntario}/reativar` também exige o middleware `administrador`.
- O middleware considera administrador quem possui cargo com slug `administrador`.
- `HandleInertiaRequests` compartilha `eh_administrador` com o frontend.
- Ao compartilhar o usuário autenticado, `HandleInertiaRequests` carrega `id`, `cidade_base_id` e `nome_completo` do voluntário. O `nome_completo` é necessário para serializar o accessor de compatibilidade `Voluntario::name`.
- O `PainelLayout` deve ocultar links administrativos de usuários comuns. Isso melhora a navegação, mas o middleware continua sendo a proteção efetiva.

---

## Validações importantes

- O e-mail deve ser único tanto em `voluntarios` quanto em `users`.
- Na edição, as regras ignoram o voluntário e o usuário vinculados.
- O CPF opcional possui índice único; valores vazios devem ser persistidos como `null`.
- O e-mail apresentado na conclusão precisa ser idêntico ao e-mail do convite.
- Telefone e CPF são normalizados para o formato usado pelo sistema antes da validação.
- Alterações que atualizem pessoa e conta devem permanecer dentro de transação.

---

## Arquivos centrais

- `app/Models/User.php`
- `app/Models/Voluntario.php`
- `app/Models/VoluntarioAfastamento.php`
- `app/Models/ConviteCadastro.php`
- `app/Enums/StatusAfastamento.php`
- `app/Enums/MotivoAfastamento.php`
- `app/Http/Controllers/Web/VoluntarioController.php`
- `app/Http/Controllers/Web/ConviteCadastroController.php`
- `app/Http/Controllers/Web/Voluntario/Afastamento/Controller.php`
- `app/Http/Requests/Web/Voluntario/Afastamento/StoreRequest.php`
- `app/Http/Requests/Web/Voluntario/Afastamento/UpdateRequest.php`
- `app/Http/Requests/Web/Voluntario/Afastamento/ProrrogarRequest.php`
- `app/Http/Requests/Web/Voluntario/Afastamento/EncerrarRequest.php`
- `app/Services/Voluntario/Service.php`
- `app/Services/Voluntario/Afastamento/Service.php`
- `app/Queries/Voluntario/Queries.php`
- `app/Queries/Voluntario/Afastamento/Queries.php`
- `app/Http/Middleware/UserAdministrador.php`
- `resources/js/Pages/Voluntario/`
- `resources/js/components/Painel/Voluntario/Listagem/AfastamentoModal.tsx`
- `resources/js/Pages/Convites/CompletarCadastro.tsx`
- `resources/js/layouts/PainelLayout.tsx`
- `routes/web.php`

Migrations principais:

- `2026_06_08_000000_create_voluntarios_table_and_link_users.php`
- `2026_06_08_010000_create_convites_cadastro_table.php`
- `2026_06_23_000000_add_complementary_fields_to_voluntarios_table.php`
- `2026_06_23_010000_extend_pending_invites_to_seven_days.php`
- `2026_08_15_000000_create_voluntario_afastamentos_table.php`

---

## Como validar alterações futuras

```bash
php artisan migrate
php artisan migrate:status
php artisan route:list
npm run types
npm run build
```

Verificação manual:

1. Entrar como administrador e abrir `/dashboard`.
2. Abrir `/voluntarios`, criar um cadastro direto e conferir cargos.
3. Criar um convite e abrir o link gerado.
4. Concluir o formulário e entrar com a nova senha.
5. Confirmar que um voluntário comum abre `/dashboard`.
6. Confirmar que esse usuário não vê links administrativos e recebe `403` ao acessar diretamente `/voluntarios`.
7. Confirmar que o administrador continua vendo e acessando voluntários, hospitais e patrocinadores.

---

## Pendências e cuidados futuros

- Definir armazenamento antes de ativar foto de perfil.
- Criar uma política explícita para impedir login de contas inativas ou futuramente bloqueadas.
- Decidir se `data_entrada_ong` representa a data do convite ou a data de ativação; hoje representa a ativação/cadastro direto.
- Avaliar validação matemática do CPF além do formato.
- Avaliar remoção futura dos campos legados de convite em `users` somente após migração e período de compatibilidade.
- Manter testes de autorização para usuário comum e administrador quando o runner PHPUnit estiver disponível.
