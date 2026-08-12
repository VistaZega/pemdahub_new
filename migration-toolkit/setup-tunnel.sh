#!/bin/bash
#=============================================================================
# PembdaHUB — Setup Cloudflare Tunnel Script
# Membuat server lokal bisa diakses dari internet tanpa port forwarding
#
# CARA PAKAI: sudo ./setup-tunnel.sh
#=============================================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

DOMAIN="perguruanpembda.com"
TUNNEL_NAME="pembdahub"

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   PembdaHUB — Setup Cloudflare Tunnel                   ║"
echo "║   Akses server lokal dari internet (GRATIS)              ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${CYAN}PRASYARAT:${NC}"
echo "  1. Sudah punya akun Cloudflare (daftar di https://dash.cloudflare.com)"
echo "  2. Domain $DOMAIN sudah ditambahkan ke Cloudflare"
echo "  3. Nameserver domain sudah diganti ke Cloudflare"
echo ""
read -p "Apakah semua prasyarat di atas sudah terpenuhi? (y/n): " READY
if [ "$READY" != "y" ] && [ "$READY" != "Y" ]; then
    echo -e "\n${YELLOW}Silakan selesaikan prasyarat dulu, lalu jalankan script ini lagi.${NC}"
    echo ""
    echo -e "${CYAN}PANDUAN:${NC}"
    echo "  1. Buka https://dash.cloudflare.com/sign-up → daftar"
    echo "  2. Add a site → ketik: $DOMAIN → pilih Free plan"
    echo "  3. Cloudflare akan kasih 2 nameserver"
    echo "  4. Di panel Hostinger → Domain → DNS/Nameservers → ganti ke nameserver Cloudflare"
    echo "  5. Tunggu propagasi (1-24 jam), lalu jalankan script ini lagi"
    exit 0
fi

# ─── Install cloudflared ─────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [1/5] Installing cloudflared...${NC}"
if command -v cloudflared &>/dev/null; then
    echo -e "${GREEN}   cloudflared sudah terinstall: $(cloudflared --version)${NC}"
else
    curl -L --output /tmp/cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
    dpkg -i /tmp/cloudflared.deb
    echo -e "${GREEN}✅ cloudflared terinstall${NC}"
fi

# ─── Login ───────────────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [2/5] Login ke Cloudflare...${NC}"
echo -e "${CYAN}Akan muncul URL. Buka URL tersebut di browser, pilih domain $DOMAIN, lalu klik Authorize.${NC}"
echo ""
cloudflared tunnel login
echo -e "${GREEN}✅ Login berhasil${NC}"

# ─── Create Tunnel ───────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [3/5] Membuat tunnel...${NC}"

# Cek apakah tunnel sudah ada
EXISTING=$(cloudflared tunnel list 2>/dev/null | grep "$TUNNEL_NAME" | awk '{print $1}' || true)
if [ -n "$EXISTING" ]; then
    TUNNEL_ID="$EXISTING"
    echo -e "${YELLOW}   Tunnel '$TUNNEL_NAME' sudah ada dengan ID: $TUNNEL_ID${NC}"
else
    TUNNEL_OUTPUT=$(cloudflared tunnel create $TUNNEL_NAME 2>&1)
    TUNNEL_ID=$(echo "$TUNNEL_OUTPUT" | grep -oP '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}' | head -1)
    echo -e "${GREEN}✅ Tunnel dibuat dengan ID: $TUNNEL_ID${NC}"
fi

if [ -z "$TUNNEL_ID" ]; then
    echo -e "${RED}❌ Gagal mendapatkan Tunnel ID. Coba jalankan manual:${NC}"
    echo "   cloudflared tunnel create $TUNNEL_NAME"
    exit 1
fi

# ─── Configure Tunnel ───────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [4/5] Mengkonfigurasi tunnel...${NC}"

# Cari credentials file
CRED_FILE=""
for dir in /root/.cloudflared ~/.cloudflared /etc/cloudflared; do
    if [ -f "$dir/${TUNNEL_ID}.json" ]; then
        CRED_FILE="$dir/${TUNNEL_ID}.json"
        break
    fi
done

if [ -z "$CRED_FILE" ]; then
    echo -e "${RED}❌ Credentials file tidak ditemukan untuk tunnel $TUNNEL_ID${NC}"
    echo "   Cari manual: find / -name '${TUNNEL_ID}.json' 2>/dev/null"
    exit 1
fi

mkdir -p /etc/cloudflared

# Copy credentials jika belum di /etc/cloudflared
if [ ! -f "/etc/cloudflared/${TUNNEL_ID}.json" ]; then
    cp "$CRED_FILE" "/etc/cloudflared/${TUNNEL_ID}.json"
fi

cat > /etc/cloudflared/config.yml << EOF
tunnel: ${TUNNEL_ID}
credentials-file: /etc/cloudflared/${TUNNEL_ID}.json

ingress:
  - hostname: ${DOMAIN}
    service: http://localhost:80
  - hostname: "*.${DOMAIN}"
    service: http://localhost:80
  - service: http_status:404
EOF

echo -e "${GREEN}✅ Config ditulis ke /etc/cloudflared/config.yml${NC}"

# ─── Route DNS ───────────────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ [5/5] Setting up DNS route...${NC}"
cloudflared tunnel route dns $TUNNEL_NAME $DOMAIN 2>/dev/null || \
    echo -e "${YELLOW}   DNS route mungkin sudah ada (OK)${NC}"
echo -e "${GREEN}✅ DNS route configured${NC}"

# ─── Install as Service ─────────────────────────────────────────────────────
echo -e "\n${YELLOW}━━━ Installing as system service...${NC}"
cloudflared service install 2>/dev/null || true
systemctl enable cloudflared
systemctl restart cloudflared

echo -e "\n${BLUE}╔═══════════════════════════════════════════════════════════╗"
echo "║         ✅ CLOUDFLARE TUNNEL AKTIF!                      ║"
echo "╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "Status: $(systemctl is-active cloudflared)"
echo ""
echo -e "${GREEN}Server Anda sekarang bisa diakses dari internet:${NC}"
echo -e "  🌐 https://$DOMAIN"
echo ""
echo -e "${YELLOW}LANGKAH TERAKHIR:${NC}"
echo -e "  1. Update APP_URL di .env:"
echo -e "     ${GREEN}sudo nano /var/www/pembdahub/.env${NC}"
echo -e "     Ubah APP_URL=https://$DOMAIN"
echo -e ""
echo -e "  2. Rebuild cache:"
echo -e "     ${GREEN}sudo ./optimize-laravel.sh${NC}"
echo -e ""
echo -e "  3. Test dari HP (pakai data seluler, matikan WiFi):"
echo -e "     Buka ${GREEN}https://$DOMAIN${NC}"
