#!/bin/bash

set -e  # Sai imediatamente em caso de erro

# Configuração de Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Nome dos serviços e arquivo compose
COMPOSE_FILE="docker-compose-prd.yml"
APP_SERVICE="laravel.app"
DB_SERVICE="mysql.db"

# Funções auxiliares
print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }
# ---------------------------------------------

# Função principal de espera por Healthcheck
wait_for_healthcheck() {
    local service_name=$1
    local max_retries=15
    local interval=5
    local i=0

    print_info "Aguardando o serviço ${service_name} ficar 'healthy'..."

    while [ $i -lt $max_retries ]; do
        STATUS=$(docker inspect --format='{{json .State.Health}}' "${service_name}" | grep -o '"Status":"[^"]*"' | cut -d':' -f2 | tr -d '"')
        
        if [ "$STATUS" == "healthy" ]; then
            print_success "Serviço ${service_name} está HEALTHY."
            return 0
        fi
        
        # Ignora "starting" e "unhealthy" nos primeiros passos, a menos que as retries acabem
        if [ "$STATUS" == "starting" ] || [ "$STATUS" == "unhealthy" ]; then
            print_info "Status atual de ${service_name}: ${STATUS}. Tentativa $((i+1))/${max_retries}..."
        fi

        sleep $interval
        i=$((i+1))
    done

    print_error "Serviço ${service_name} não atingiu o status 'healthy' após $(($max_retries * $interval)) segundos."
    docker-compose -f ${COMPOSE_FILE} logs ${service_name}
    exit 1
}
# ---------------------------------------------

# --- Pré-Checks ---
if [ ! -f .env.production ]; then print_error "Arquivo .env.production não encontrado!"; exit 1; fi
if grep -q "APP_KEY=base64:GERAR_COM" .env.production; then print_error "APP_KEY não foi gerada!"; exit 1; fi

print_info "Iniciando deploy da aplicação PNSL-NTM..."

# 1. Para e remove containers existentes (substitua por 'up --force-recreate' para Zero Downtime)
print_info "Parando e removendo containers existentes..."
docker-compose -f ${COMPOSE_FILE} down
print_success "Containers parados"

# 2. Build da nova imagem
print_info "Construindo nova imagem Docker..."
# Garante que o build use o Dockerfile.production correto se o compose não especificar
docker-compose -f ${COMPOSE_FILE} build --no-cache
print_success "Imagem construída com sucesso"

# 3. Sobe os containers (sem a flag down, para Zero Downtime, mas aqui mantemos o padrão)
print_info "Iniciando containers e volumes..."
docker-compose -f ${COMPOSE_FILE} up -d --remove-orphans
print_success "Containers iniciados"

# 4. Aguarda Healthchecks
# Note: Usamos o nome real do container para o 'docker inspect', o docker-compose ps pode ajudar a descobrir
# Vamos assumir o nome padrão do compose: <project_name>-<service_name>-<instance>
PROJECT_NAME=$(basename $(pwd) | tr '[:upper:]' '[:lower:]' | tr -c -d '[:alnum:]_')
wait_for_healthcheck "${PROJECT_NAME}-${DB_SERVICE}-1"
wait_for_healthcheck "${PROJECT_NAME}-${APP_SERVICE}-1"

# 5. Executa migrations e comandos Artisan
print_info "Executando migrations e caches..."
# Usando o usuário 'laravel' do Dockerfile para executar comandos
ARTISAN_CMD="docker-compose -f ${COMPOSE_FILE} exec -T --user laravel ${APP_SERVICE}"

# Migrations
${ARTISAN_CMD} php artisan migrate --force
print_success "Migrations executadas"

# Cache
${ARTISAN_CMD} php artisan config:cache
${ARTISAN_CMD} php artisan route:cache
${ARTISAN_CMD} php artisan view:cache
print_success "Cache de configurações/rotas/views gerado"

# 6. Verifica logs
print_info "Últimas linhas dos logs do App:"
docker-compose -f ${COMPOSE_FILE} logs ${APP_SERVICE} --tail=20

# 7. Testa conectividade (endpoint /health deve estar configurado no Laravel)
print_info "Testando conectividade da porta 80..."
if curl -f http://localhost > /dev/null 2>&1; then
    print_success "Aplicação está respondendo!"
else
    print_warning "Aplicação não está respondendo. Verifique os logs!"
fi

# Resumo final (Mantido)
echo ""
print_success "========================================="
print_success "DEPLOY CONCLUÍDO COM SUCESSO!"
print_success "========================================="
echo ""
print_info "Acesse a aplicação em: http://localhost"
echo ""