# Security — Koding Indonesia

## Jangan pernah commit ke Git

- `.env` dan variabel production (password DB, email, `APP_KEY`)
- File test/debug sementara di `public/` (mis. `*-test.php`)
- Kredensial cPanel, FTP, SSH, atau API key
- Email pribadi kontributor / pelanggan di dokumen internal

Semua sudah tercakup di `.gitignore` (`.env` tidak ikut push). Verifikasi sebelum push:

```bash
git status
git diff --staged
```

Yang **aman** di repo publik: `.env.example` (placeholder), GA4 Measurement ID, Turnstile **site key** (public).

Yang **hanya** di server / GitHub Secrets: `APP_KEY`, `DB_PASSWORD`, `MAIL_PASSWORD`, `ADMIN_PASSWORD`, `TURNSTILE_SECRET_KEY`, `DEPLOY_HOOK_TOKEN`, kredensial FTP.

## Deploy hook (`DEPLOY_HOOK_TOKEN`)

Endpoint `/deploy/*` mengembalikan **404** tanpa token yang valid.

**Prefer header** (tidak tercatat di URL browser / riwayat):

```bash
curl -fsS -H "X-Deploy-Token: TOKEN" https://kodingindonesia.com/deploy/clear-cache
```

Query `?token=` masih didukung untuk kompatibilitas, tapi **hindari** membuka deploy hook di browser — token bisa bocor lewat history, screenshot, atau log.

Respons deploy hook **tidak** menampilkan email admin atau PII lengkap.

### Rotasi token

Jika token pernah terlihat di URL, chat, atau screenshot:

1. Generate baru: `php -r "echo bin2hex(random_bytes(32));"`
2. Update `DEPLOY_HOOK_TOKEN` di `.env` server (cPanel)
3. Update GitHub Secret `DEPLOY_HOOK_TOKEN` (environment `antonprafanto`)
4. Panggil `/deploy/clear-cache` dengan token baru

## Setup awal admin

Gunakan variabel di `.env` (lihat `.env.example`):

```
ADMIN_EMAIL=...
ADMIN_PASSWORD=...
```

Lalu `php artisan db:seed`, atau perbaiki / buat admin via:

```bash
php artisan kindo:ensure-admin --reset-password
```

**Tanpa SSH:** set `ADMIN_*` di `.env`, deploy, lalu:

```bash
curl -fsS -H "X-Deploy-Token: TOKEN" https://kodingindonesia.com/deploy/ensure-admin
```

Opsi lain: `php artisan make:filament-user`

## Production (Rumahweb)

- `.env` hanya di server — edit via cPanel File Manager, tidak di repo
- `PUBLIC_HTML_STORAGE` — path mirror upload (spesifik server)
- Rotasi password email & admin secara berkala
- Hapus file maintenance/test setelah dipakai

## Jika kredensial terlanjur terbuka

1. Ganti password / rotasi token segera (cPanel, email, admin Filament, `DEPLOY_HOOK_TOKEN`)
2. Jangan paste password atau token deploy di chat/issue publik
3. Jika sudah ter-push ke GitHub: rotasi secret + pertimbangkan `git filter-repo` untuk hapus dari history
