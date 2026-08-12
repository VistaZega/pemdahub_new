# ============================================================================
# PembdaHUB Migration Toolkit — README
# ============================================================================
#
# Toolkit ini berisi semua script yang dibutuhkan untuk migrasi PembdaHUB
# dari Hostinger ke server lokal Ubuntu Linux.
#
# ┌──────────────────────────────────────────────────────────────────────────┐
# │  URUTAN PENGGUNAAN                                                       │
# ├──────────────────────────────────────────────────────────────────────────┤
# │                                                                          │
# │  PERSIAPAN (di PC Windows):                                              │
# │  1. Download Ubuntu Server 24.04 LTS dari ubuntu.com/download/server     │
# │  2. Buat bootable USB pakai Rufus (rufus.ie)                             │
# │  3. Install Ubuntu di PC server (lihat implementation_plan.md Fase 1)    │
# │                                                                          │
# │  SETELAH UBUNTU TERINSTALL:                                              │
# │  Transfer folder migration-toolkit/ ke server Ubuntu:                    │
# │    scp -r D:\laragon\www\pembdahub\migration-toolkit\ admin@IP:/tmp/    │
# │                                                                          │
# │  DI SERVER UBUNTU (via SSH atau langsung):                               │
# │    cd /tmp/migration-toolkit                                             │
# │    chmod +x *.sh                                                         │
# │                                                                          │
# │  4. sudo ./setup-server.sh         ← Install semua software             │
# │  5. sudo mysql_secure_installation ← Amankan MySQL (manual)             │
# │  6. sudo ./create-database.sh      ← Buat database PembdaHUB            │
# │  7. sudo ./deploy-app.sh           ← Clone & deploy aplikasi            │
# │  8. Edit .env:  sudo nano /var/www/pembdahub/.env                       │
# │     - Isi DB_PASSWORD                                                    │
# │     - Copy WHATSAPP_API_TOKEN, dll dari Hostinger                        │
# │  9. Export database dari Hostinger (phpMyAdmin)                          │
# │     Transfer ke server: scp backup.sql admin@IP:/tmp/                    │
# │ 10. sudo ./import-database.sh /tmp/backup.sql                           │
# │ 11. Export file upload dari Hostinger (zip folder storage/app/public/)   │
# │     Transfer & extract di server                                         │
# │ 12. sudo ./optimize-laravel.sh     ← Cache & optimize                   │
# │ 13. TEST LOKAL: buka http://IP_SERVER dari browser PC lain               │
# │ 14. sudo ./setup-tunnel.sh         ← Cloudflare Tunnel (akses internet) │
# │ 15. TEST INTERNET: buka https://perguruanpembda.com dari HP              │
# │                                                                          │
# │  SETELAH MIGRASI:                                                        │
# │  - sudo ./health-check.sh   ← Cek status semua service                  │
# │  - sudo ./update-app.sh     ← Update setelah ada perubahan di GitHub     │
# │                                                                          │
# └──────────────────────────────────────────────────────────────────────────┘
#
# DAFTAR FILE:
#
#   setup-server.sh     - Install PHP, MySQL, Nginx, Node.js, dll
#   create-database.sh  - Buat database & user MySQL
#   deploy-app.sh       - Clone repo, install deps, setup Nginx & Supervisor
#   import-database.sh  - Import database SQL dari Hostinger
#   optimize-laravel.sh - Cache config, routes, views untuk production
#   setup-tunnel.sh     - Setup Cloudflare Tunnel (akses internet)
#   health-check.sh     - Cek status semua service & resources
#   update-app.sh       - Update aplikasi dari GitHub
#
# ============================================================================
