#!/bin/bash

# ============================================
# Script de Deploy para Produção
# ============================================
# Este script automatiza o processo de deploy
# da aplicação Laravel em ambiente Docker
# ============================================

set -e  # Para execução em caso de erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Funções auxiliares
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Verifica se .env.production existe
if [ ! -f .env.production ]; then
    print_error "Arquivo .env.production não encontrado!"
    print_info "Copie .env.production.example para .env.production e configure as variáveis"
    exit 1
fi

# Verifica se APP_KEY está configurada
if grep -q "APP_KEY=base64:GERAR_COM" .env.production; then
    print_error "APP_KEY não foi gerada!"
    print_info "Execute: php artisan key:generate --env=production"
    exit 1
fi

print_info "Iniciando deploy da aplicação PNSL-NTM..."

# 1. Para containers existentes
print_info "Parando containers existentes..."
docker-compose -f docker-compose.production.yml down
print_success "Containers parados"

# 2. Remove imagens antigas (opcional)
read -p "Deseja remover imagens antigas? (s/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Ss]$ ]]; then
    print_info "Removendo imagens antigas..."
    docker image prune -f
    print_success "Imagens antigas removidas"
fi

# 3. Build da nova imagem
print_info "Construindo nova imagem Docker..."
docker-compose -f docker-compose.production.yml build --no-cache
print_success "Imagem construída com sucesso"

# 4. Sobe os containers
print_info "Iniciando containers..."
docker-compose -f docker-compose.production.yml up -d
print_success "Containers iniciados"

# 5. Aguarda containers ficarem saudáveis
print_info "Aguardando containers ficarem saudáveis..."
sleep 10

# 6. Verifica status dos containers
print_info "Verificando status dos containers..."
docker-compose -f docker-compose.production.yml ps

# 7. Executa migrations
print_info "Executando migrations do banco de dados..."
docker-compose -f docker-compose.production.yml exec -T laravel.app php artisan migrate --force
print_success "Migrations executadas"

# 8. Cache de configurações
print_info "Gerando cache de configurações..."
docker-compose -f docker-compose.production.yml exec -T laravel.app php artisan config:cache
docker-compose -f docker-compose.production.yml exec -T laravel.app php artisan route:cache
docker-compose -f docker-compose.production.yml exec -T laravel.app php artisan view:cache
print_success "Cache gerado"

# 9. Otimiza autoloader
print_info "Otimizando autoloader..."
docker-compose -f docker-compose.production.yml exec -T laravel.app composer dump-autoload --optimize
print_success "Autoloader otimizado"

# 10. Limpa caches antigos (se necessário)
print_info "Limpando caches da aplicação..."
docker-compose -f docker-compose.production.yml exec -T laravel.app php artisan cache:clear
print_success "Caches limpos"

# 11. Verifica logs
print_info "Últimas linhas dos logs:"
docker-compose -f docker-compose.production.yml logs --tail=20

# 12. Testa conectividade
print_info "Testando conectividade..."
if curl -f http://localhost/health > /dev/null 2>&1; then
    print_success "Aplicação está respondendo!"
else
    print_warning "Aplicação não está respondendo no endpoint /health"
    print_info "Verifique os logs com: docker-compose -f docker-compose.production.yml logs -f"
fi

# Resumo final
echo ""
print_success "========================================="
print_success "Deploy concluído com sucesso!"
print_success "========================================="
echo ""
print_info "Comandos úteis:"
echo "  - Ver logs: docker-compose -f docker-compose.production.yml logs -f"
echo "  - Status: docker-compose -f docker-compose.production.yml ps"
echo "  - Parar: docker-compose -f docker-compose.production.yml down"
echo "  - Reiniciar: docker-compose -f docker-compose.production.yml restart"
echo ""
print_info "Acesse a aplicação em: http://localhost"
echo ""
