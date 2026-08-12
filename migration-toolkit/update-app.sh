#!/bin/bash
#=============================================================================
# PembdaHUB — Update Application Script
# Pull perubahan terbaru dari GitHub dan deploy
#
# CARA PAKAI: sudo ./update-app.sh
#=============================================================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

APP_DIR="/var/www/pembdahub"

echo -e "${BLUE}━━━ Update PembdaHUB ━━━${NC}"
echo ""

cd $APP_DIR

# Maintenance mode
echo -e "${YELLOW}Mengaktifkan maintenance mode...${NC}"
sudo -u www-data php artisan down --retry=60 || true

# Pull latest code
echo -e "${YELLOW}Pulling dari GitHub...${NC}"
sudo -u www-data git pull origin main

# Install composer dependencies
echo -e "${YELLOW}Updating PHP dependencies...${NC}"
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

# Install npm & build
echo -e "${YELLOW}Building frontend assets...${NC}"
npm install
npm run build

# Run migrations
echo -e "${YELLOW}Running migrations...${NC}"
sudo -u www-data php artisan migrate --force

# Cache
echo -e "${YELLOW}Rebuilding cache...${NC}"
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

# Restart workers
echo -e "${YELLOW}Restarting queue workers...${NC}"
supervisorctl restart pembdahub-worker:* 2>/dev/null || true

# Exit maintenance mode
echo -e "${YELLOW}Menonaktifkan maintenance mode...${NC}"
sudo -u www-data php artisan up

echo -e "\n${GREEN}✅ Update selesai!${NC}"
echo -e "Test: buka ${GREEN}http://$(hostname -I | awk '{print $1}')${NC}"
