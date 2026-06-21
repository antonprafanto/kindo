# Panduan Deployment ke Rumahweb

## Persiapan

### 1. Buat Database di cPanel Rumahweb
1. Login ke cPanel → **MySQL Databases**
2. Buat database baru: misal `kodi0941_kodingindonesia`
3. Buat user database dan set password kuat
4. Assign user ke database dengan ALL PRIVILEGES
5. Catat: DB_DATABASE, DB_USERNAME, DB_PASSWORD

### 2. Setup SSH Key (Opsional tapi Disarankan)
```bash
# Generate key pair di laptop
ssh-keygen -t ed25519 -C "kodingindonesia"
# Copy public key ke ~/.ssh/authorized_keys di server
```

---

## Deployment Pertama

### Step 1: SSH ke Server
```bash
ssh kodi0941@202.10.43.90
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

```bash
ssh kodi0941@202.10.43.90
cd ~/kodingindonesia
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

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

## Post-Deploy Checklist

- [ ] Buka `https://kodingindonesia.com` — pastikan HTTPS aktif
- [ ] Test homepage, artikel, kontak
- [ ] Test Filament admin: `https://kodingindonesia.com/admin`
- [ ] Submit sitemap ke Google Search Console: `https://kodingindonesia.com/sitemap.xml`
- [ ] Aktifkan daily backup di Rumahweb cPanel
- [ ] Pasang Google Analytics 4 (tambah GA4 ID di `.env`)
