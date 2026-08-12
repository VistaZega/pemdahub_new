#!/bin/bash
#=============================================================================
# PembdaHUB — Deploy Application Script
# Jalankan SETELAH create-database.sh
#
# CARA PAKAI: sudo ./deploy-app.sh
#=============================================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

APP_DIR="/var/www/pembdahub"
REPO_URL="https://github.com/YulianusZega/new_pembdahub.git"

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║        PembdaHUB — Deploy Application                   ║"
echo "║        Fase 3: Clone, Install, Configure                 ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# ─── Clone Repository ────────────────────────────────────────────────────────
echo -e "${YELLOW}━━━ [1/6] Cloning repository dari GitHub...${NC}"
if [ -d "$APP_DIR" ]; then
    echo -e "${YELLOW}   Direktori sudah ada, melakukan git pull...${NC}"
    cd $APP_DIR
    git pull origin main
else
    mkdir -p /var/www
    git clone $REPO_URL $APP_DIR
fi
chown -R www-data:www-data $APP_DIR
echo -e "${GREEN}✅ Repository berhasil di-clone${NC}"

# ─── Install Composer Dependencies ───────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [2/6] Installing PHP dependencies (composer)...${NC}"
cd $APP_DIR
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✅ Composer dependencies terinstall${NC}"

# ─── Setup Environment ──────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [3/6] Setting up environment...${NC}"
if [ ! -f "$APP_DIR/.env" ]; then
    cp $APP_DIR/.env.example $APP_DIR/.env
    echo -e "${YELLOW}   File .env dibuat dari .env.example${NC}"
    echo -e "${RED}   ⚠️  PENTING: Edit .env dengan: sudo nano $APP_DIR/.env${NC}"
    echo -e "${RED}   ⚠️  Isi DB_PASSWORD, WHATSAPP_API_TOKEN, dll dari Hostinger .env${NC}"
else
    echo -e "${YELLOW}   File .env sudah ada, tidak dioverwrite${NC}"
fi

# Generate app key
sudo -u www-data php artisan key:generate --force
echo -e "${GREEN}✅ App key generated${NC}"

# Storage link
sudo -u www-data php artisan storage:link --force 2>/dev/null || true
echo -e "${GREEN}✅ Storage link created${NC}"

# ─── Set Permissions ─────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [4/6] Setting permissions...${NC}"
chown -R www-data:www-data $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache
echo -e "${GREEN}✅ Permissions set${NC}"

# ─── Build Frontend ─────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [5/6] Building frontend assets (npm)...${NC}"
cd $APP_DIR
npm install
npm run build
echo -e "${GREEN}✅ Frontend assets built${NC}"

# ─── Setup Nginx ─────────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [6/6] Setting up Nginx...${NC}"

# Cek apakah config file dari toolkit ada
TOOLKIT_DIR="$(cd "$(dirname "$0")" && pwd)"
if [ -f "$TOOLKIT_DIR/nginx-pembdahub.conf" ]; then
    cp "$TOOLKIT_DIR/nginx-pembdahub.conf" /etc/nginx/sites-available/pembdahub
else
    # Buat config inline
    cat > /etc/nginx/sites-available/pembdahub << 'NGINX_CONF'
server {
    listen 80;
    server_name perguruanpembda.com _;
    root /var/www/pembdahub/public;

    index index.php index.html;

    charset utf-8;
    client_max_body_size 50M;

    access_log /var/log/nginx/pembdahub-access.log;
    error_log  /var/log/nginx/pembdahub-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}
NGINX_CONF
fi

ln -sf /etc/nginx/sites-available/pembdahub /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl restart nginx
echo -e "${GREEN}✅ Nginx configured and restarted${NC}"

# ─── Setup Supervisor ────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ Setting up Supervisor (Queue Worker)...${NC}"
cat > /etc/supervisor/conf.d/pembdahub-worker.conf << 'SUPERVISOR_CONF'
[program:pembdahub-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pembdahub/artisan queue:work database --queue=default --tries=3 --timeout=90 --max-jobs=1000 --sleep=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/pembdahub/storage/logs/worker.log
stopwaitsecs=3600
SUPERVISOR_CONF

supervisorctl reread
supervisorctl update
echo -e "${GREEN}✅ Supervisor configured${NC}"

# ─── Setup Cron ──────────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ Setting up Cron (Task Scheduler)...${NC}"
CRON_LINE="* * * * * cd /var/www/pembdahub && php artisan schedule:run >> /dev/null 2>&1"
(crontab -u www-data -l 2>/dev/null | grep -v "schedule:run"; echo "$CRON_LINE") | crontab -u www-data -
echo -e "${GREEN}✅ Cron job configured${NC}"

# ─── Selesai ─────────────────────────────────────────────────────────────────
echo -e "\n${BLUE}╔═══════════════════════════════════════════════════════════╗"
echo "║              ✅ DEPLOY SELESAI!                          ║"
echo "╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}LANGKAH SELANJUTNYA:${NC}"
echo -e "  1. Edit .env:        ${GREEN}sudo nano /var/www/pembdahub/.env${NC}"
echo -e "     - Isi DB_PASSWORD dengan password database yang Anda buat"
echo -e "     - Copy WHATSAPP_API_TOKEN, GEMINI_API_KEY, dll dari Hostinger"
echo -e ""
echo -e "  2. Import database:  ${GREEN}sudo ./import-database.sh /tmp/pembdahub_backup.sql${NC}"
echo -e "     - Transfer file SQL dari Hostinger dulu ke /tmp/"
echo -e ""
echo -e "  3. Import file upload dari Hostinger"
echo -e "     - Transfer zip ke /tmp/ lalu:"
echo -e "     ${GREEN}cd /var/www/pembdahub/storage/app/public/ && sudo unzip /tmp/storage_uploads.zip${NC}"
echo -e "     ${GREEN}sudo chown -R www-data:www-data /var/www/pembdahub/storage/app/public/${NC}"
echo -e ""
echo -e "  4. Cache & optimize: ${GREEN}sudo ./optimize-laravel.sh${NC}"
echo -e ""
echo -e "  5. Test akses lokal: Buka ${GREEN}http://$(hostname -I | awk '{print $1}')${NC} dari browser PC lain"
echo ""
