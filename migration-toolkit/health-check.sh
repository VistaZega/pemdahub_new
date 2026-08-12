#!/bin/bash
#=============================================================================
# PembdaHUB — Health Check Script
# Cek status semua service dan tampilkan ringkasan
#
# CARA PAKAI: sudo ./health-check.sh
#=============================================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║         PembdaHUB — Health Check                         ║"
echo "║         $(date '+%Y-%m-%d %H:%M:%S')                             ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

check_service() {
    local name=$1
    local service=$2
    if systemctl is-active --quiet $service 2>/dev/null; then
        echo -e "  ${GREEN}✅ $name${NC} — running"
    else
        echo -e "  ${RED}❌ $name${NC} — NOT running!"
        echo -e "     Fix: ${YELLOW}sudo systemctl start $service${NC}"
    fi
}

# ─── Services ────────────────────────────────────────────────────────────────
echo -e "${YELLOW}━━━ Status Services ━━━${NC}"
check_service "Nginx" "nginx"
check_service "PHP-FPM" "php8.2-fpm"
check_service "MySQL" "mysql"
check_service "Cloudflare Tunnel" "cloudflared"

# Supervisor/Queue Worker
echo -n "  "
WORKER_STATUS=$(supervisorctl status pembdahub-worker:* 2>/dev/null | head -1 | awk '{print $2}')
if [ "$WORKER_STATUS" = "RUNNING" ]; then
    WORKER_COUNT=$(supervisorctl status pembdahub-worker:* 2>/dev/null | grep -c RUNNING)
    echo -e "${GREEN}✅ Queue Worker${NC} — $WORKER_COUNT proses running"
else
    echo -e "${RED}❌ Queue Worker${NC} — NOT running!"
    echo -e "     Fix: ${YELLOW}sudo supervisorctl start pembdahub-worker:*${NC}"
fi

# ─── Cron ────────────────────────────────────────────────────────────────────
echo -n "  "
CRON_EXISTS=$(crontab -u www-data -l 2>/dev/null | grep -c "schedule:run" || true)
if [ "$CRON_EXISTS" -gt 0 ]; then
    echo -e "${GREEN}✅ Cron (Scheduler)${NC} — configured"
else
    echo -e "${RED}❌ Cron (Scheduler)${NC} — NOT configured!"
fi

# ─── Disk Usage ──────────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ Penggunaan Disk ━━━${NC}"
DISK_USAGE=$(df -h / | tail -1 | awk '{print $5}')
DISK_AVAIL=$(df -h / | tail -1 | awk '{print $4}')
DISK_PCT=${DISK_USAGE%\%}
if [ "$DISK_PCT" -gt 90 ]; then
    echo -e "  ${RED}⚠️  Disk: $DISK_USAGE terpakai (tersisa $DISK_AVAIL) — HAMPIR PENUH!${NC}"
elif [ "$DISK_PCT" -gt 75 ]; then
    echo -e "  ${YELLOW}⚠️  Disk: $DISK_USAGE terpakai (tersisa $DISK_AVAIL)${NC}"
else
    echo -e "  ${GREEN}✅ Disk: $DISK_USAGE terpakai (tersisa $DISK_AVAIL)${NC}"
fi

# ─── RAM Usage ───────────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ Penggunaan RAM ━━━${NC}"
RAM_TOTAL=$(free -h | grep Mem | awk '{print $2}')
RAM_USED=$(free -h | grep Mem | awk '{print $3}')
RAM_AVAIL=$(free -h | grep Mem | awk '{print $7}')
RAM_PCT=$(free | grep Mem | awk '{printf "%.0f", $3/$2*100}')
if [ "$RAM_PCT" -gt 90 ]; then
    echo -e "  ${RED}⚠️  RAM: $RAM_USED / $RAM_TOTAL (tersisa $RAM_AVAIL) — HAMPIR PENUH!${NC}"
elif [ "$RAM_PCT" -gt 75 ]; then
    echo -e "  ${YELLOW}⚠️  RAM: $RAM_USED / $RAM_TOTAL (tersisa $RAM_AVAIL)${NC}"
else
    echo -e "  ${GREEN}✅ RAM: $RAM_USED / $RAM_TOTAL (tersisa $RAM_AVAIL)${NC}"
fi

# ─── Laravel Status ──────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ Status Laravel ━━━${NC}"
APP_DIR="/var/www/pembdahub"

# Test database connection
DB_OK=$(cd $APP_DIR && sudo -u www-data php artisan tinker --execute="try{DB::connection()->getPdo();echo 'OK';}catch(\Exception \$e){echo 'FAIL';}" 2>/dev/null || echo "FAIL")
if [ "$DB_OK" = "OK" ]; then
    echo -e "  ${GREEN}✅ Database connection${NC} — OK"
else
    echo -e "  ${RED}❌ Database connection${NC} — FAILED!"
fi

# Storage writable
if [ -w "$APP_DIR/storage/logs" ]; then
    echo -e "  ${GREEN}✅ Storage writable${NC} — OK"
else
    echo -e "  ${RED}❌ Storage NOT writable${NC}"
    echo -e "     Fix: ${YELLOW}sudo chown -R www-data:www-data $APP_DIR/storage${NC}"
fi

# Queue pending jobs
QUEUE_COUNT=$(cd $APP_DIR && sudo -u www-data php artisan tinker --execute="echo DB::table('jobs')->count();" 2>/dev/null || echo "?")
echo -e "  📋 Pending queue jobs: ${CYAN}$QUEUE_COUNT${NC}"

FAILED_COUNT=$(cd $APP_DIR && sudo -u www-data php artisan tinker --execute="echo DB::table('failed_jobs')->count();" 2>/dev/null || echo "?")
if [ "$FAILED_COUNT" != "0" ] && [ "$FAILED_COUNT" != "?" ]; then
    echo -e "  ${YELLOW}⚠️  Failed jobs: $FAILED_COUNT${NC}"
fi

# ─── Recent Errors ──────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ Error Terakhir (5 baris) ━━━${NC}"
LOG_FILE="$APP_DIR/storage/logs/laravel.log"
if [ -f "$LOG_FILE" ]; then
    ERROR_COUNT=$(grep -c "ERROR\|CRITICAL\|EMERGENCY" "$LOG_FILE" 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo -e "  ${YELLOW}Total error di log: $ERROR_COUNT${NC}"
        echo -e "  ${YELLOW}5 error terakhir:${NC}"
        grep "ERROR\|CRITICAL\|EMERGENCY" "$LOG_FILE" | tail -5 | while read line; do
            echo -e "    ${RED}$line${NC}"
        done
    else
        echo -e "  ${GREEN}✅ Tidak ada error di log${NC}"
    fi
else
    echo -e "  ${YELLOW}Log file belum ada${NC}"
fi

# ─── Web Access Test ─────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ Test Akses Web ━━━${NC}"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    echo -e "  ${GREEN}✅ HTTP localhost${NC} — Response: $HTTP_CODE"
else
    echo -e "  ${RED}❌ HTTP localhost${NC} — Response: $HTTP_CODE"
fi

echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}Health check selesai.${NC}"
echo ""
