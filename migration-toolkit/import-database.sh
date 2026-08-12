#!/bin/bash
#=============================================================================
# PembdaHUB — Import Database Script
# Import database SQL dari Hostinger ke MySQL lokal
#
# CARA PAKAI: sudo ./import-database.sh /path/to/backup.sql
#=============================================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

if [ -z "$1" ]; then
    echo -e "${RED}❌ Cara pakai: sudo ./import-database.sh /path/to/file.sql${NC}"
    echo "   Contoh:    sudo ./import-database.sh /tmp/pembdahub_backup.sql"
    exit 1
fi

SQL_FILE="$1"

if [ ! -f "$SQL_FILE" ]; then
    echo -e "${RED}❌ File tidak ditemukan: $SQL_FILE${NC}"
    exit 1
fi

FILE_SIZE=$(du -h "$SQL_FILE" | cut -f1)
echo -e "${BLUE}━━━ Import Database PembdaHUB ━━━${NC}"
echo -e "File: ${GREEN}$SQL_FILE${NC} (${FILE_SIZE})"
echo ""

read -p "Masukkan password database user 'pembdahub': " -s DB_PASS
echo ""

echo -e "\n${YELLOW}Mengimport database... (mungkin butuh beberapa menit untuk database besar)${NC}"

# Import dengan progress indicator
pv "$SQL_FILE" 2>/dev/null | mysql -u pembdahub -p"$DB_PASS" pembdahub 2>/dev/null || \
mysql -u pembdahub -p"$DB_PASS" pembdahub < "$SQL_FILE"

echo -e "\n${GREEN}✅ Database berhasil diimport!${NC}"

# Verifikasi
echo -e "\n${YELLOW}━━━ Verifikasi Data ━━━${NC}"
echo -e "Menghitung jumlah data..."

TABLES=$(mysql -u pembdahub -p"$DB_PASS" pembdahub -N -e "SHOW TABLES;" 2>/dev/null | wc -l)
echo -e "  Total tabel:    ${GREEN}$TABLES${NC}"

STUDENTS=$(mysql -u pembdahub -p"$DB_PASS" pembdahub -N -e "SELECT COUNT(*) FROM students;" 2>/dev/null || echo "N/A")
echo -e "  Jumlah siswa:   ${GREEN}$STUDENTS${NC}"

TEACHERS=$(mysql -u pembdahub -p"$DB_PASS" pembdahub -N -e "SELECT COUNT(*) FROM teachers;" 2>/dev/null || echo "N/A")
echo -e "  Jumlah guru:    ${GREEN}$TEACHERS${NC}"

USERS=$(mysql -u pembdahub -p"$DB_PASS" pembdahub -N -e "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "N/A")
echo -e "  Jumlah users:   ${GREEN}$USERS${NC}"

AY=$(mysql -u pembdahub -p"$DB_PASS" pembdahub -N -e "SELECT COUNT(*) FROM academic_years;" 2>/dev/null || echo "N/A")
echo -e "  Tahun Pelajaran: ${GREEN}$AY${NC}"

echo -e "\n${YELLOW}PENTING: Bandingkan angka-angka di atas dengan data di Hostinger!${NC}"
echo -e "Jika jumlahnya sama → migrasi data berhasil ✅"
echo ""

# Jalankan migrasi Laravel untuk memastikan tabel up-to-date
echo -e "${YELLOW}Menjalankan Laravel migrate...${NC}"
cd /var/www/pembdahub
sudo -u www-data php artisan migrate --force
echo -e "${GREEN}✅ Laravel migrations up-to-date${NC}"

echo -e "\n${YELLOW}LANGKAH SELANJUTNYA:${NC}"
echo -e "  1. Import file upload dari Hostinger"
echo -e "  2. Jalankan: ${GREEN}sudo ./optimize-laravel.sh${NC}"
echo -e "  3. Test akses: buka http://$(hostname -I | awk '{print $1}') dari browser"
