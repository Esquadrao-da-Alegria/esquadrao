#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

PROJETO="esquadrao"
SERVICO_DOCKER="mysql"
PASTA_BACKUPS="/tmp/mysql-backups"
PASTA_DOWNLOADS="${HOME}/Downloads"
ARQUIVO_CONTAINER="/tmp/backup.sql"
PADRAO_BACKUP="backup_${PROJETO}_*.sql"

LIMPAR=false
RECRIAR_BANCO=true
SEM_DEFINER=false
USAR_BACKUP_LOCAL=false
ARQUIVO_EXPLICITO=""
ARQUIVO_REMOTO=""

if [[ -f "$SCRIPT_DIR/import-db-prod.local.env" ]]; then
    # shellcheck source=/dev/null
    source "$SCRIPT_DIR/import-db-prod.local.env"
fi

exibir_erro() {
    echo "❌ $1" >&2
}

exibir_info() {
    echo "→ $1"
}

exibir_sucesso() {
    echo "✅ $1"
}

exibir_uso() {
    cat <<EOF
Importação do banco de dados — produção → local (Ubuntu + Docker)

Fluxo padrão (sem opções):
  1. Conecta no servidor de produção via SSH
  2. Gera dump com mysqldump (credenciais do .env remoto)
  3. Baixa o arquivo localmente
  4. Drop + create do banco local
  5. Importa no container Docker

Uso:
  ./scripts/import-db-prod.sh [opções]

Opções:
  --arquivo PATH       Importa um .sql local (pula conexão remota)
  --apenas-local       Usa o backup mais recente em /tmp/mysql-backups ou ~/Downloads
  --limpar             Remove arquivos de backup após importação confirmada
  --sem-recriar-banco  Não faz drop/create antes de importar
  --sem-definer        Remove DEFINERs do dump (alternativa ao SUPER privilege)
  --help               Exibe esta ajuda

Configure scripts/import-db-prod.local.env
(copie de scripts/import-db-prod.env.example).

Exemplos:
  ./scripts/import-db-prod.sh
  ./scripts/import-db-prod.sh --limpar
  ./scripts/import-db-prod.sh --arquivo ~/Downloads/backup_esquadrao_030826_120000.sql
EOF
}

ler_variavel_env() {
    local variavel="$1"
    local valor

    valor="$(grep "^${variavel}=" "$PROJECT_DIR/.env" | cut -d '=' -f2- | sed 's/^"\(.*\)"$/\1' | sed "s/^'\(.*\)'$/\1")"

    if [[ -z "$valor" ]]; then
        exibir_erro "Variável ${variavel} não encontrada no .env local"
        exit 1
    fi

    printf '%s' "$valor"
}

carregar_credenciais_locais() {
    if [[ ! -f "$PROJECT_DIR/.env" ]]; then
        exibir_erro "Arquivo .env não encontrado em ${PROJECT_DIR}"
        exit 1
    fi

    DB_DATABASE="$(ler_variavel_env DB_DATABASE)"
    DB_USERNAME="$(ler_variavel_env DB_USERNAME)"
    DB_PASSWORD="$(ler_variavel_env DB_PASSWORD)"
}

validar_config_ssh() {
    local chave="${IMPORT_SSH_KEY:-}"
    local usuario="${IMPORT_SSH_USER:-}"
    local host="${IMPORT_SSH_HOST:-}"
    local caminho_remoto="${IMPORT_REMOTE_PATH:-}"

    if [[ -z "$chave" || -z "$usuario" || -z "$host" || -z "$caminho_remoto" ]]; then
        exibir_erro "Configure IMPORT_SSH_KEY, IMPORT_SSH_USER, IMPORT_SSH_HOST e IMPORT_REMOTE_PATH"
        exibir_erro "Copie scripts/import-db-prod.env.example → scripts/import-db-prod.local.env"
        exit 1
    fi

    if [[ ! -f "$chave" ]]; then
        exibir_erro "Chave SSH não encontrada: ${chave}"
        exit 1
    fi
}

obter_opcoes_ssh() {
    SSH_OPTS=(-i "${IMPORT_SSH_KEY}" -p "${IMPORT_SSH_PORT:-22}" -o BatchMode=yes)
    SSH_DESTINO="${IMPORT_SSH_USER}@${IMPORT_SSH_HOST}"
}

verificar_pre_requisitos() {
    if ! command -v docker >/dev/null 2>&1; then
        exibir_erro "Docker não encontrado. Instale Docker e inicie o serviço."
        exit 1
    fi

    if ! command -v ssh >/dev/null 2>&1 || ! command -v scp >/dev/null 2>&1; then
        exibir_erro "ssh e scp são necessários para o fluxo remoto."
        exit 1
    fi

    cd "$PROJECT_DIR"

    if [[ ! -f "docker-compose.yml" ]]; then
        exibir_erro "docker-compose.yml não encontrado em ${PROJECT_DIR}"
        exit 1
    fi

    if ! docker compose ps "$SERVICO_DOCKER" --status running -q | grep -q .; then
        exibir_erro "Container '${SERVICO_DOCKER}' não está em execução. Rode: docker compose up -d"
        exit 1
    fi
}

remover_backup_remoto() {
    if [[ -z "$ARQUIVO_REMOTO" ]]; then
        return 0
    fi

    obter_opcoes_ssh
    ssh "${SSH_OPTS[@]}" "$SSH_DESTINO" "rm -f '${ARQUIVO_REMOTO}'" || true
    ARQUIVO_REMOTO=""
}

gerar_backup_remoto() {
    validar_config_ssh
    obter_opcoes_ssh

    local timestamp
    local caminho_remoto="${IMPORT_REMOTE_PATH}"

    timestamp="$(date +"%d%m%y_%H%M%S")"
    ARQUIVO_REMOTO="${caminho_remoto}/backup_${PROJETO}_${timestamp}.sql"

    exibir_info "Conectando em ${IMPORT_SSH_HOST} e gerando backup..."

    if ! ssh "${SSH_OPTS[@]}" "$SSH_DESTINO" bash -s -- "$caminho_remoto" "$PROJETO" "$ARQUIVO_REMOTO" <<'REMOTE_SCRIPT'
set -euo pipefail

CAMINHO_REMOTO="$1"
PROJETO="$2"
ARQUIVO_BACKUP="$3"

cd "$CAMINHO_REMOTO"

if [[ ! -f .env ]]; then
    echo "Arquivo .env não encontrado em ${CAMINHO_REMOTO}" >&2
    exit 1
fi

ler_env_remoto() {
    grep "^$1=" .env | cut -d '=' -f2- | sed 's/^"\(.*\)"$/\1' | sed "s/^'\(.*\)'$/\1"
}

DB_DATABASE="$(ler_env_remoto DB_DATABASE)"
DB_USERNAME="$(ler_env_remoto DB_USERNAME)"
DB_PASSWORD="$(ler_env_remoto DB_PASSWORD)"
DB_HOST="$(ler_env_remoto DB_HOST)"

if [[ -z "$DB_HOST" ]]; then
    DB_HOST="127.0.0.1"
fi

if [[ -z "$DB_DATABASE" || -z "$DB_USERNAME" || -z "$DB_PASSWORD" ]]; then
    echo "Credenciais DB incompletas no .env remoto" >&2
    exit 1
fi

if ! command -v mysqldump >/dev/null 2>&1; then
    echo "mysqldump não encontrado no servidor remoto" >&2
    exit 1
fi

mysqldump \
    --single-transaction \
    --skip-lock-tables \
    --no-tablespaces \
    --routines \
    --triggers \
    --events \
    --default-character-set=utf8mb4 \
    -h "$DB_HOST" \
    -u "$DB_USERNAME" \
    -p"$DB_PASSWORD" \
    "$DB_DATABASE" \
    > "$ARQUIVO_BACKUP"

du -sh "$ARQUIVO_BACKUP" | cut -f1
REMOTE_SCRIPT
    then
        exibir_erro "Falha ao gerar backup no servidor remoto"
        remover_backup_remoto
        exit 1
    fi

    exibir_sucesso "Backup gerado no servidor remoto"
}

baixar_backup_remoto() {
    obter_opcoes_ssh

    local timestamp
    local arquivo_local

    timestamp="$(basename "$ARQUIVO_REMOTO" | sed "s/backup_${PROJETO}_//" | sed 's/.sql$//')"
    mkdir -p "$PASTA_BACKUPS"
    arquivo_local="${PASTA_BACKUPS}/backup_${PROJETO}_${timestamp}.sql"

    exibir_info "Baixando backup remoto..."

    if ! scp -i "${IMPORT_SSH_KEY}" -P "${IMPORT_SSH_PORT:-22}" \
        "${SSH_DESTINO}:${ARQUIVO_REMOTO}" "$arquivo_local"; then
        exibir_erro "Falha ao baixar backup. O arquivo remoto pode precisar ser removido manualmente."
        exit 1
    fi

    exibir_info "Removendo backup do servidor remoto..."
    remover_backup_remoto

    ARQUIVO_EXPLICITO="$arquivo_local"
    exibir_sucesso "Backup local: ${arquivo_local} ($(du -sh "$arquivo_local" | cut -f1))"
}

sincronizar_backup_remoto() {
    gerar_backup_remoto
    baixar_backup_remoto
}

localizar_backup_mais_recente() {
    local candidato=""

    mkdir -p "$PASTA_BACKUPS"

    candidato="$(ls -t "${PASTA_BACKUPS}"/${PADRAO_BACKUP} 2>/dev/null | head -1 || true)"

    if [[ -z "$candidato" ]]; then
        candidato="$(ls -t "${PASTA_DOWNLOADS}"/${PADRAO_BACKUP} 2>/dev/null | head -1 || true)"

        if [[ -n "$candidato" ]]; then
            cp "$candidato" "${PASTA_BACKUPS}/"
            candidato="$(ls -t "${PASTA_BACKUPS}"/${PADRAO_BACKUP} 2>/dev/null | head -1 || true)"
        fi
    fi

    if [[ -z "$candidato" ]]; then
        exibir_erro "Nenhum backup local encontrado (${PADRAO_BACKUP})"
        exit 1
    fi

    ARQUIVO_EXPLICITO="$candidato"
}

preparar_arquivo_importacao() {
    local origem="$1"
    local destino="${PASTA_BACKUPS}/backup_import.sql"

    if [[ "$origem" == *.gz ]]; then
        exibir_info "Descompactando ${origem}..."
        gunzip -c "$origem" > "$destino"
    elif [[ "$SEM_DEFINER" == true ]]; then
        exibir_info "Removendo DEFINERs do dump..."
        sed 's/\sDEFINER=`[^`]*`@`[^`]*`//g' "$origem" > "$destino"
    else
        cp "$origem" "$destino"
    fi

    ARQUIVO_IMPORTACAO="$destino"
}

recriar_banco() {
    exibir_info "Recriando banco local '${DB_DATABASE}' (drop + create)..."

    docker compose exec -T "$SERVICO_DOCKER" mysql -h 127.0.0.1 -u root -p"${DB_PASSWORD}" <<-EOSQL
        DROP DATABASE IF EXISTS \`${DB_DATABASE}\`;
        CREATE DATABASE \`${DB_DATABASE}\`;
        GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'%';
        FLUSH PRIVILEGES;
EOSQL

    exibir_sucesso "Banco local recriado"
}

habilitar_routines() {
    docker compose exec -T "$SERVICO_DOCKER" mysql -h 127.0.0.1 -u root -p"${DB_PASSWORD}" \
        -e "SET GLOBAL log_bin_trust_function_creators = 1;"
}

desabilitar_routines() {
    docker compose exec -T "$SERVICO_DOCKER" mysql -h 127.0.0.1 -u root -p"${DB_PASSWORD}" \
        -e "SET GLOBAL log_bin_trust_function_creators = 0;" || true
}

importar_backup() {
    exibir_info "Copiando dump para o container..."

    docker compose cp "$ARQUIVO_IMPORTACAO" "${SERVICO_DOCKER}:${ARQUIVO_CONTAINER}"

    exibir_info "Importando banco '${DB_DATABASE}' ($(du -sh "$ARQUIVO_IMPORTACAO" | cut -f1))..."

    habilitar_routines

    if docker compose exec "$SERVICO_DOCKER" bash -c \
        "command -v pv >/dev/null 2>&1 && pv ${ARQUIVO_CONTAINER} | mysql -h 127.0.0.1 -u '${DB_USERNAME}' -p'${DB_PASSWORD}' '${DB_DATABASE}' || mysql -h 127.0.0.1 -u '${DB_USERNAME}' -p'${DB_PASSWORD}' '${DB_DATABASE}' < ${ARQUIVO_CONTAINER}"; then
        desabilitar_routines
    else
        desabilitar_routines
        exibir_erro "Falha na importação. Tente --sem-definer"
        exit 1
    fi

    exibir_sucesso "Importação concluída"
}

verificar_importacao() {
    exibir_info "Verificando tabelas importadas..."

    local total
    total="$(docker compose exec -T "$SERVICO_DOCKER" mysql -h 127.0.0.1 -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" \
        -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '${DB_DATABASE}';")"

    if [[ "${total:-0}" -eq 0 ]]; then
        exibir_erro "Nenhuma tabela encontrada após importação"
        exit 1
    fi

    docker compose exec -T "$SERVICO_DOCKER" mysql -h 127.0.0.1 -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" \
        -e "SHOW TABLES;"

    exibir_sucesso "${total} tabelas importadas"
}

limpar_backups_locais() {
    exibir_info "Removendo arquivos de backup locais..."

    docker compose exec -T "$SERVICO_DOCKER" rm -f "$ARQUIVO_CONTAINER" || true
    rm -f "${PASTA_BACKUPS}"/${PADRAO_BACKUP}
    rm -f "${PASTA_BACKUPS}/backup_import.sql"
    rm -f "${PASTA_DOWNLOADS}"/${PADRAO_BACKUP}

    exibir_sucesso "Backups locais removidos"
}

processar_argumentos() {
    while [[ $# -gt 0 ]]; do
        case "$1" in
            --arquivo)
                ARQUIVO_EXPLICITO="$2"
                shift 2
                ;;
            --apenas-local)
                USAR_BACKUP_LOCAL=true
                shift
                ;;
            --limpar)
                LIMPAR=true
                shift
                ;;
            --sem-recriar-banco)
                RECRIAR_BANCO=false
                shift
                ;;
            --sem-definer)
                SEM_DEFINER=true
                shift
                ;;
            --help|-h)
                exibir_uso
                exit 0
                ;;
            *)
                exibir_erro "Opção desconhecida: $1"
                exibir_uso
                exit 1
                ;;
        esac
    done
}

main() {
    processar_argumentos "$@"

    verificar_pre_requisitos
    carregar_credenciais_locais

    if [[ -n "$ARQUIVO_EXPLICITO" ]]; then
        exibir_info "Modo: importar arquivo local (--arquivo)"
    elif [[ "$USAR_BACKUP_LOCAL" == true ]]; then
        exibir_info "Modo: backup local existente (--apenas-local)"
        localizar_backup_mais_recente
    else
        exibir_info "Modo: produção → local (SSH + dump + download + import)"
        sincronizar_backup_remoto
    fi

    if [[ ! -f "$ARQUIVO_EXPLICITO" ]]; then
        exibir_erro "Arquivo não encontrado: ${ARQUIVO_EXPLICITO}"
        exit 1
    fi

    exibir_info "Usando backup: ${ARQUIVO_EXPLICITO}"

    preparar_arquivo_importacao "$ARQUIVO_EXPLICITO"

    if [[ "$RECRIAR_BANCO" == true ]]; then
        recriar_banco
    fi

    importar_backup
    verificar_importacao

    if [[ "$LIMPAR" == true ]]; then
        limpar_backups_locais
    else
        exibir_info "Backups locais mantidos. Use --limpar para remover após confirmar."
    fi

    exibir_sucesso "Banco local sincronizado com produção"
}

main "$@"
