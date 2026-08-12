# Esquadrão AI Reviewer

O **Esquadrão AI Reviewer** é uma GitHub Action desenvolvida para realizar revisões automáticas e informativas em Pull Requests destinadas à branch `dev`.

O objetivo principal é antecipar dúvidas, verificar a aderência às regras do projeto e auxiliar no onboarding de novos desenvolvedores, mantendo a consistência do código sem bloquear o fluxo de desenvolvimento.

---

## 🎯 Escopo e Funcionamento

- **Gatilho:** Executado automaticamente quando uma PR para `dev` é aberta, reaberta, recebe novos commits (`synchronize`) ou é marcada como pronta para revisão (`ready_for_review`).
- **Comportamento:**
  - **100% Informativo:** Não aprova (`APPROVE`), não solicita alterações formais (`REQUEST_CHANGES`), não bloqueia merges e não altera código.
  - **Tolerante a falhas:** Executado com `continue-on-error: true`. Caso ocorra qualquer falha na API ou conexão, a pipeline prossegue normalmente.
  - **Sem duplicação de ruído:** Atualiza o comentário principal existente na PR a cada novos commits.

---

## 🔑 Configuração da Chave da API (GEMINI_API_KEY)

Para que o reviewer consiga realizar a análise, é necessário configurar a chave da API do Google Gemini no repositório do GitHub:

1. Acesse o [Google AI Studio](https://aistudio.google.com/) e crie uma **API Key** (gratuita no Free Tier).
2. No repositório do GitHub, vá em **Settings** > **Secrets and variables** > **Actions**.
3. Clique em **New repository secret**.
4. Defina:
   - **Name:** `GEMINI_API_KEY`
   - **Secret:** *(Cole a chave gerada no Google AI Studio)*
5. Clique em **Add secret**.

*Nota: Se a secret não estiver configurada, o workflow executará sem falhar, mas emitirá um aviso nos logs indicando que a revisão foi ignorada.*

---

## 📚 Fonte de Regras do Reviewer

O AI Reviewer analisa as alterações comparando o diff da PR com os seguintes arquivos de regras versionados no projeto:

- [`AGENTS.md`](../AGENTS.md) — Diretrizes arquiteturais, padrões de código, nomenclaturas e Services/Queries.
- Arquivos de documentação em [`docs/`](./) (ex: `docs/regras/geral.md`, `docs/features/`).

### Regra Fundamental da IA
> O AI Reviewer **nunca pode inventar regras de negócio**. Caso uma regra não esteja documentada nos arquivos acima, o reviewer apresentará o ponto como **dúvida/sugestão** (utilizando o emoji ⚠️) para validação humana.

---

## 🛠️ Manutenção e Customização

- **Workflow:** Configurado em [`.github/workflows/ai-reviewer.yml`](../.github/workflows/ai-reviewer.yml).
- **Script da revisão:** Localizado em [`.github/scripts/ai-reviewer.js`](../.github/scripts/ai-reviewer.js).
- **Adicionando novas regras:** Basta criar ou atualizar arquivos Markdown dentro do diretório `docs/`. O script lê automaticamente todos os documentos `.md` da pasta `docs/` ao realizar a análise.
