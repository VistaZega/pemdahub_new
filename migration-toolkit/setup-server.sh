#!/bin/bash
#=============================================================================
# PembdaHUB Migration Toolkit — Setup Script
# Script ini menginstall semua software yang dibutuhkan di Ubuntu Server
#
# CARA PAKAI:
#   1. Copy file ini ke server Ubuntu (via SCP atau flashdisk)
#   2. Jalankan: chmod +x setup-server.sh && sudo ./setup-server.sh
#=============================================================================

set -e  # Stop jika ada error

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║        PembdaHUB — Server Setup Script                   ║"
echo "║        Fase 2: Install Software Server                   ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Cek apakah dijalankan sebagai root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Script harus dijalankan sebagai root (sudo)!${NC}"
    echo "   Jalankan: sudo ./setup-server.sh"
    exit 1
fi

# ─── Fase 2.1: Update Sistem ─────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [1/7] Mengupdate sistem...${NC}"
apt update && apt upgrade -y
echo -e "${GREEN}✅ Sistem terupdate${NC}"

# ─── Fase 2.2: Install PHP 8.2 ───────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [2/7] Menginstall PHP 8.2 + ekstensi...${NC}"
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-gd php8.2-curl php8.2-zip php8.2-bcmath \
    php8.2-tokenizer php8.2-fileinfo php8.2-intl php8.2-readline

# Optimasi PHP-FPM untuk 1000+ siswa
echo -e "${YELLOW}   Mengoptimasi PHP-FPM...${NC}"
PHP_FPM_CONF="/etc/php/8.2/fpm/pool.d/www.conf"
if [ -f "$PHP_FPM_CONF" ]; then
    sed -i 's/^pm = .*/pm = dynamic/' $PHP_FPM_CONF
    sed -i 's/^pm.max_children = .*/pm.max_children = 20/' $PHP_FPM_CONF
    sed -i 's/^pm.start_servers = .*/pm.start_servers = 5/' $PHP_FPM_CONF
    sed -i 's/^pm.min_spare_servers = .*/pm.min_spare_servers = 3/' $PHP_FPM_CONF
    sed -i 's/^pm.max_spare_servers = .*/pm.max_spare_servers = 10/' $PHP_FPM_CONF
fi

# Optimasi php.ini
PHP_INI="/etc/php/8.2/fpm/php.ini"
if [ -f "$PHP_INI" ]; then
    sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' $PHP_INI
    sed -i 's/^post_max_size = .*/post_max_size = 50M/' $PHP_INI
    sed -i 's/^memory_limit = .*/memory_limit = 256M/' $PHP_INI
    sed -i 's/^max_execution_time = .*/max_execution_time = 300/' $PHP_INI
fi

systemctl restart php8.2-fpm
systemctl enable php8.2-fpm
echo -e "${GREEN}✅ PHP 8.2 terinstall dan dioptimasi${NC}"

# ─── Fase 2.3: Install MySQL 8.0 ─────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [3/7] Menginstall MySQL 8.0...${NC}"
apt install -y mysql-server
systemctl enable mysql

# Optimasi MySQL untuk 1000+ siswa
MYSQL_CONF="/etc/mysql/mysql.conf.d/mysqld.cnf"
if [ -f "$MYSQL_CONF" ]; then
    cat >> $MYSQL_CONF << 'EOF'

# === PembdaHUB Optimization ===
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
max_connections = 200
query_cache_type = 0
tmp_table_size = 64M
max_heap_table_size = 64M
join_buffer_size = 2M
sort_buffer_size = 2M
EOF
    systemctl restart mysql
fi

echo -e "${GREEN}✅ MySQL 8.0 terinstall${NC}"
echo -e "${RED}⚠️  PENTING: Jalankan 'sudo mysql_secure_installation' setelah script ini selesai!${NC}"
echo -e "${RED}⚠️  Kemudian buat database dengan menjalankan: sudo ./create-database.sh${NC}"

# ─── Fase 2.4: Install Nginx ─────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [4/7] Menginstall Nginx...${NC}"
apt install -y nginx
systemctl enable nginx
echo -e "${GREEN}✅ Nginx terinstall${NC}"

# ─── Fase 2.5: Install Composer ──────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [5/7] Menginstall Composer...${NC}"
cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
echo -e "${GREEN}✅ Composer terinstall${NC}"

# ─── Fase 2.6: Install Node.js 20 LTS ────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [6/7] Menginstall Node.js 20 LTS...${NC}"
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
echo -e "${GREEN}✅ Node.js terinstall${NC}"

# ─── Fase 2.7: Install Tools Pendukung ───────────────────────────────────────
echo -e "\n${YELLOW}━━━ [7/7] Menginstall Supervisor, Git, tools lainnya...${NC}"
apt install -y supervisor git unzip cron
systemctl enable supervisor
systemctl enable cron
echo -e "${GREEN}✅ Tools pendukung terinstall${NC}"

# ─── Verifikasi ──────────────────────────────────────────────────────────────
echo -e "\n${BLUE}╔═══════════════════════════════════════════════════════════╗"
echo "║              ✅ INSTALASI SELESAI!                        ║"
echo "╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}Versi yang terinstall:${NC}"
echo "  PHP:       $(php -v 2>/dev/null | head -1)"
echo "  MySQL:     $(mysql --version 2>/dev/null)"
echo "  Nginx:     $(nginx -v 2>&1)"
echo "  Node.js:   $(node --version 2>/dev/null)"
echo "  npm:       $(npm --version 2>/dev/null)"
echo "  Composer:  $(composer --version 2>/dev/null | head -1)"
echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}LANGKAH SELANJUTNYA:${NC}"
echo -e "  1. Jalankan: ${GREEN}sudo mysql_secure_installation${NC}"
echo -e "  2. Jalankan: ${GREEN}sudo ./create-database.sh${NC}"
echo -e "  3. Jalankan: ${GREEN}sudo ./deploy-app.sh${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
