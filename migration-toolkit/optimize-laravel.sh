#!/bin/bash
#=============================================================================
# PembdaHUB — Optimize Laravel Script
# Cache config, routes, views untuk production performance
#
# CARA PAKAI: sudo ./optimize-laravel.sh
#=============================================================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

APP_DIR="/var/www/pembdahub"

echo -e "${BLUE}━━━ Optimasi Laravel Production ━━━${NC}"
echo ""

cd $APP_DIR

echo -e "${YELLOW}Clearing old cache...${NC}"
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan event:clear

echo -e "${YELLOW}Building cache...${NC}"
sudo -u www-data php artisan config:cache
echo -e "${GREEN}  ✅ Config cached${NC}"

sudo -u www-data php artisan route:cache
echo -e "${GREEN}  ✅ Routes cached${NC}"

sudo -u www-data php artisan view:cache
echo -e "${GREEN}  ✅ Views cached${NC}"

sudo -u www-data php artisan event:cache
echo -e "${GREEN}  ✅ Events cached${NC}"

# Restart services
echo -e "${YELLOW}Restarting services...${NC}"
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl restart pembdahub-worker:* 2>/dev/null || true

echo -e "\n${GREEN}✅ Laravel dioptimasi untuk production!${NC}"
echo ""
echo -e "${YELLOW}Test akses:${NC} Buka ${GREEN}http://$(hostname -I | awk '{print $1}')${NC} dari browser"
