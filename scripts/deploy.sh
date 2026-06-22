#!/bin/bash
# Deploy update ke Rumahweb — jalankan via cPanel Terminal atau SSH
# Usage: bash scripts/deploy.sh
set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> Project: $(pwd)"
echo "==> Pull latest dari GitHub..."
git pull origin main

echo "==> Cek perbaikan form kontak..."
if grep -q "contactSubject" app/Mail/ContactMail.php; then
    echo "✅ ContactMail fix ditemukan"
else
    echo "❌ ContactMail masih versi lama — cek path project atau branch git"
    exit 1
fi

echo "==> Clear & rebuild cache..."
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ Deploy selesai! Tes form kontak di:"
echo "   https://kodingindonesia.com/kontak"
