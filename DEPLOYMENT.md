# Panduan Deployment ke Rumahweb

## Persiapan

### 1. Buat Database di cPanel Rumahweb
1. Login ke cPanel → **MySQL Databases**
2. Buat database baru: misal `cpaneluser_dbname`
3. Buat user database dan set password kuat
4. Assign user ke database dengan ALL PRIVILEGES
5. Catat: DB_DATABASE, DB_USERNAME, DB_PASSWORD

### 2. Setup SSH Key (Opsional — hanya paket Unlimited M ke atas)
```bash
# Generate key pair di laptop
ssh-keygen -t ed25519 -C "kodingindonesia"
# Copy public key ke ~/.ssh/authorized_keys di server
```

> **Unlimited S tidak punya SSH.** Untuk deploy otomatis, gunakan **GitHub Actions + FTP** (lihat bagian bawah).

---

## Deploy Otomatis — GitHub Actions + FTP (Unlimited S)

Tanpa SSH, deploy dilakukan via **FTP** dari GitHub Actions setiap push ke `main`.

### Alur kerja

```
Push ke main → GitHub Actions
  → composer install + npm build
  → upload via FTP ke Rumahweb
  → hapus cache config/routes
  → panggil /deploy/clear-cache (webhook)
```

### Step 1: Siapkan FTP di cPanel

1. Login cPanel → **FTP Accounts**
2. Catat:
   - **Server**: biasanya `ftp.kodingindonesia.com` atau hostname IIX Rumahweb
   - **Username**: akun FTP cPanel (mis. `kodi0941`)
   - **Password**: password FTP
   - **Port**: `21`
3. Catat **path folder Laravel** di server (root yang berisi `artisan`), misalnya:
   - `/public_html/kodingindonesia/`
   - atau `/kindo/`

> Buka File Manager → cari file `artisan` → path itulah `FTP_SERVER_DIR` (harus diakhiri `/`).

### Step 2: Tambah GitHub Secrets

Repo: `https://github.com/antonprafanto/kindo` → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

| Secret | Contoh nilai |
|--------|----------------|
| `FTP_SERVER` | `ftp.kodingindonesia.com` |
| `FTP_USERNAME` | `kodi0941` |
| `FTP_PASSWORD` | password FTP cPanel |
| `FTP_PORT` | `21` |
| `FTP_SERVER_DIR` | `/public_html/kodingindonesia/` |
| `DEPLOY_HOOK_TOKEN` | string acak 64 karakter (generate di bawah) |

> Secrets bisa disimpan di **Repository secrets** atau **Environment secrets**.
> Jika pakai Environment, nama environment harus `antonprafanto` (sesuai workflow).

Generate token deploy hook:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

### Rotasi `DEPLOY_HOOK_TOKEN` (disarankan berkala)

Jika token pernah terlihat di log, screenshot, atau chat — ganti segera. **Jangan commit token ke repo.**

Urutan yang benar (hindari hook 404 saat deploy):

1. Generate token baru (perintah di atas)
2. **Production dulu:** cPanel File Manager → edit `~/kindo/.env` → ganti baris `DEPLOY_HOOK_TOKEN=...`
3. **GitHub:** repo **Settings → Secrets and variables → Actions** → environment `antonprafanto` → edit `DEPLOY_HOOK_TOKEN`
4. Verifikasi: buka `https://kodingindonesia.com/deploy/clear-cache?token=TOKEN_BARU` — harus return `Cache cleared` (bukan 404)
5. Push/deploy berikutnya memakai token baru otomatis dari GitHub secret

Token lama langsung tidak berlaku setelah langkah 2–3 selesai.

### Step 3: Set token di server (.env production)

Via cPanel **File Manager**, edit `.env` di folder Laravel, tambahkan:
```
DEPLOY_HOOK_TOKEN=token_yang_sama_dengan_github_secret
```

### Step 4: Deploy pertama (manual sekali)

Karena workflow baru, jalankan sekali dari GitHub:
1. Buka tab **Actions** di repo GitHub
2. Pilih workflow **Deploy to Rumahweb (FTP)**
3. Klik **Run workflow** → **Run workflow**

Atau push commit apa pun ke `main`.

### Step 5: Verifikasi

1. Cek tab **Actions** — status hijau ✅
2. Tes https://kodingindonesia.com/kontak (form kontak)
3. Deploy berikutnya otomatis setiap `git push origin main`

### Batasan (Unlimited S)

| Bisa via CI/CD | Tidak bisa tanpa SSH |
|----------------|----------------------|
| Upload kode PHP, views, config | `php artisan migrate` |
| Build & upload assets Vite | Composer di server |
| Clear cache via deploy hook | SSH / Terminal |

Untuk migration database: jalankan sekali via **cPanel → Terminal** (jika tersedia) atau upgrade ke Unlimited M.

---

## Filament / Livewire 403 saat simpan artikel (ModSecurity WAF)

Gejala: notifikasi *"Error while loading page"*, console `POST /livewire-…/update 403 (Forbidden)` saat menyimpan artikel yang berisi cuplikan kode (ESP32, MQTT, shell).

**Penyebab:** ModSecurity di Rumahweb memblokir payload POST Livewire (false positive), bukan bug Laravel/Filament.

**Perbaikan otomatis (sudah di repo):** `public/.htaccess` mematikan WAF untuk URL `/livewire*` dan `/edit-body`. Isi artikel panjang disimpan lewat **Editor Isi Artikel** (tombol biru di halaman edit Filament) — form biasa dengan payload base64, bukan Livewire.

**Jika masih 403 setelah deploy:**

1. Login **cPanel** → cari **ModSecurity**
2. Temukan domain `kodingindonesia.com` → toggle **Off** (atau nonaktifkan rule ID tertentu jika support Rumahweb memberi ID dari error log)
3. Hard refresh admin (`Ctrl+Shift+R`) → coba simpan lagi

> Catatan: menonaktifkan ModSecurity untuk seluruh domain sedikit mengurangi lapisan WAF, tapi umum dipakai situs Laravel + Filament di shared hosting. Alternatif jangka panjang: VPS dengan kontrol WAF lebih granular.

---

## Deployment Pertama

### Step 1: SSH ke Server
```bash
ssh USER@SERVER_IP -p 2223
```

### Step 2: Clone Repository
```bash
cd ~
git clone https://github.com/[USERNAME]/kodingindonesia.git
cd kodingindonesia
```

### Step 3: Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### Step 4: Konfigurasi Environment
```bash
cp .env.example .env
nano .env   # atau vi .env
```

Isi semua variabel:
- `APP_KEY=` — akan di-generate di step 5
- `DB_DATABASE=`, `DB_USERNAME=`, `DB_PASSWORD=`
- `MAIL_*` — SMTP credentials Rumahweb

### Step 5: Generate App Key
```bash
php artisan key:generate
```

### Step 6: Jalankan Migrations & Seeder
```bash
php artisan migrate --force
php artisan db:seed --force
```

### Step 7: Storage Link
```bash
php artisan storage:link
```

### Step 8: Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 644 storage/framework/cache
```

### Step 9: Cache Config (Production)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 10: Set Document Root di cPanel
1. Login cPanel → **Domains** → klik domain `kodingindonesia.com`
2. Set **Document Root** ke: `public_html/kodingindonesia/public`
   (atau sesuai path tempat clone)
3. Save

### Step 11: Build Assets (jika tidak commit public/build)
```bash
# Jika Node.js tersedia di server
npm install && npm run build
# Jika tidak, upload public/build/ dari lokal via FTP/SCP
```

---

## Deployment Selanjutnya (Update)

**Otomatis (disarankan — Unlimited S):** push ke `main` → GitHub Actions deploy via FTP.

**Manual via SSH** (hanya Unlimited M+):
```bash
ssh USER@SERVER_IP -p 2223
cd ~/kindo
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

**Manual via cPanel Git:** cPanel → **Git Version Control** → Pull or Deploy (jika tersedia).

---

## Konfigurasi Email (Rumahweb SMTP)

Di Rumahweb, buat email account di cPanel → Email Accounts:
- Email: `noreply@kodingindonesia.com`
- Server: `mail.kodingindonesia.com`
- Port: 465 (SSL) atau 587 (TLS)

Update `.env` production:
```
MAIL_MAILER=smtp
MAIL_SCHEME=ssl
MAIL_HOST=mail.kodingindonesia.com
MAIL_PORT=465
MAIL_USERNAME=noreply@kodingindonesia.com
MAIL_PASSWORD=password_email_kamu
```

---

## Backup Hosting (Rumahweb)

> **Penting:** Daily backup Rumahweb **bukan** toggle di cPanel — layanan tambahan berbayar yang diaktifkan lewat **Clientzone**. Weekly backup gratis otomatis dari Rumahweb (jika memenuhi syarat AUP).

### Weekly backup gratis (sudah termasuk paket)

Rumahweb backup mingguan otomatis **selama**:
- Disk usage **< 5 GB**
- Inodes **< 75.000**

Cek di cPanel → **Disk Usage**. Jika melebihi limit, backup mingguan bisa dihentikan (email notifikasi dari Rumahweb).

### Daily backup (berbayar, ~Rp 9.900/bulan)

Untuk backup **setiap hari** (disarankan untuk website aktif):

1. Login **[Clientzone Rumahweb](https://clientzone.rumahweb.com)**
2. Menu **Hosting** → **Manage** (paket `kodingindonesia.com`)
3. Bagian **Daily Backup** → klik **Beli**
4. Pilih paket kapasitas — minimal **2× disk usage** saat ini (mis. paket 10 GB cukup untuk situs kecil)
5. Checkout & bayar

Setelah aktif:
- Kelola backup: Clientzone → Hosting → Manage → **Login Backup**
- Download: **Request Download** pada tanggal yang diinginkan
- Restore full: upload ke cPanel + buka tiket support Rumahweb

### Backup manual di cPanel (gratis, kapan saja)

Sebelum perubahan besar (deploy, migration, hapus data):

1. Login cPanel → **Backup** (atau **Backup Wizard**)
2. **Download a Full Account Backup** → Generate → tunggu link download
3. Simpan file `.tar.gz` di komputer / cloud pribadi

Backup partial (lebih cepat):
- **Home Directory** — file website (`kindo/`, `public_html/`)
- **MySQL Database** — pilih database `kodingindonesia`

### Checklist backup Koding Indonesia

- [ ] Verifikasi weekly backup aktif (disk < 5 GB, inodes < 75k)
- [ ] Beli & aktifkan Daily Backup di Clientzone *(opsional tapi disarankan)*
- [ ] Download 1 full backup manual ke komputer (baseline)
- [ ] Catat tanggal backup terakhir di catatan pribadi

---

## Post-Deploy Checklist

- [ ] Buka `https://kodingindonesia.com` — pastikan HTTPS aktif
- [ ] Test homepage, artikel, kontak
- [ ] Test Filament admin: `https://kodingindonesia.com/admin`
- [ ] Submit sitemap ke Google Search Console: `https://kodingindonesia.com/sitemap.xml`
- [ ] Aktifkan Daily Backup di Clientzone Rumahweb *(lihat bagian Backup di atas)*
- [ ] Pasang Google Analytics 4 (tambah GA4 ID di `.env`)
