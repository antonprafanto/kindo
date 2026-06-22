# Security — Koding Indonesia

## Jangan pernah commit ke Git

- `.env` dan variabel production (password DB, email, `APP_KEY`)
- File test/debug sementara di `public/` (mis. `*-test.php`)
- Kredensial cPanel, FTP, SSH, atau API key

Semua sudah tercakup di `.gitignore`. Verifikasi sebelum push:

```bash
git status
git diff --staged
```

## Setup awal admin

Gunakan variabel di `.env` (lihat `.env.example`):

```
ADMIN_EMAIL=...
ADMIN_PASSWORD=...
```

Lalu `php artisan db:seed`, atau buat user via:

```bash
php artisan make:filament-user
```

## Production (Rumahweb)

- `.env` hanya di server — edit via FTP/SSH, tidak di repo
- `PUBLIC_HTML_STORAGE` — path mirror upload (spesifik server)
- Rotasi password email & admin secara berkala
- Hapus file maintenance/test setelah dipakai

## Jika kredensial terlanjur terbuka

1. Ganti password segera (cPanel, email, admin Filament)
2. Jangan paste password di chat/issue publik
3. Jika sudah ter-push ke GitHub: rotasi secret + pertimbangkan `git filter-repo` untuk hapus dari history
