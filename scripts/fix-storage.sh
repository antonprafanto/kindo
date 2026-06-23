#!/bin/bash
# Fix cover image 404 — jalankan di server Rumahweb via cPanel Terminal atau SSH
set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> Project: $(pwd)"
echo "==> Membuat storage symlink..."
php artisan storage:link

if [ -L "public/storage" ]; then
    echo "✅ Symlink berhasil: public/storage -> $(readlink public/storage)"
else
    echo "⚠️  Symlink gagal — cek apakah hosting mengizinkan symlink"
    echo "    Fallback .htaccess di public/ seharusnya tetap bisa serve file upload"
fi

echo "==> Clear cache (jangan view:cache — bikin CSS stale)..."
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache

echo ""
echo "✅ Selesai! Cek gambar di:"
echo "   https://kodingindonesia.com/storage/articles/covers/"
