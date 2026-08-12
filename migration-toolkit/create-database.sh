#!/bin/bash
#=============================================================================
# PembdaHUB — Create Database Script
# Jalankan SETELAH setup-server.sh dan mysql_secure_installation
#
# CARA PAKAI: sudo ./create-database.sh
#=============================================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}━━━ Membuat Database PembdaHUB ━━━${NC}"
echo ""

# Minta password untuk user database
read -sp "Masukkan PASSWORD untuk user database 'pembdahub' (CATAT password ini!): " DB_PASS
echo ""
read -sp "Konfirmasi PASSWORD: " DB_PASS_CONFIRM
echo ""

if [ "$DB_PASS" != "$DB_PASS_CONFIRM" ]; then
    echo -e "${RED}❌ Password tidak cocok! Coba lagi.${NC}"
    exit 1
fi

if [ -z "$DB_PASS" ]; then
    echo -e "${RED}❌ Password tidak boleh kosong!${NC}"
    exit 1
fi

echo -e "\n${YELLOW}Membuat database dan user...${NC}"

mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS pembdahub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'pembdahub'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON pembdahub.* TO 'pembdahub'@'localhost';
FLUSH PRIVILEGES;
EOF

echo -e "${GREEN}✅ Database 'pembdahub' berhasil dibuat!${NC}"
echo -e "${GREEN}✅ User 'pembdahub'@'localhost' berhasil dibuat!${NC}"
echo ""
echo -e "${YELLOW}Simpan informasi ini:${NC}"
echo -e "  Database:  ${GREEN}pembdahub${NC}"
echo -e "  Username:  ${GREEN}pembdahub${NC}"
echo -e "  Password:  ${GREEN}(yang Anda masukkan tadi)${NC}"
echo ""
echo -e "${YELLOW}LANGKAH SELANJUTNYA:${NC}"
echo -e "  Jalankan: ${GREEN}sudo ./deploy-app.sh${NC}"
