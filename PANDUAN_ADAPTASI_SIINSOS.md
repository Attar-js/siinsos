# Panduan Adaptasi SIINSOS (Aplikasi Gabungan)

Dokumen ini untuk **mitra / device lain** yang menjalankan **SIINSOS** — produk gabungan dari `project-akhir` (landing) dan `dashboard` (admin) dalam **satu aplikasi Laravel**.

> Jika masih memakai dua folder terpisah (`project-akhir` + `dashboard`), gunakan panduan di root repo: `PANDUAN_ADAPTASI_SERVER_MITRA.md`.  
> Untuk folder **`siinsos`**, ikuti dokumen ini saja.

---

## 1. Gambaran sistem

| Aspek | Detail |
|-------|--------|
| Folder | `siinsos/` saja |
| Landing (mahasiswa/dosen) | `http://host/` |
| Admin / Tim MK Penciri | `http://host/admin` |
| Database | **Satu** MySQL (contoh: `dashboardta`) |
| Storage file | `siinsos/storage/app/public/` |

Tidak perlu folder sibling `project-akhir` atau `dashboard`.

---

## 2. Persyaratan

| Software | Versi |
|----------|--------|
| PHP | 8.2+ |
| Ekstensi | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd`, `curl` |
| Composer | 2.x |
| MySQL / MariaDB | 8.x / 10.x |
| Web server | Nginx/Apache, atau `php artisan serve` untuk uji lokal |

Node.js / `npm run build` **tidak wajib** untuk menjalankan UI (aset CSS/JS ada di `public/css` dan `public/assets`).

---

## 3. Checklist setup cepat

```bash
# 1) Masuk folder aplikasi
cd siinsos

# 2) Dependency PHP
composer install --no-dev --optimize-autoloader
# (untuk development: composer install)

# 3) Environment
copy .env.example .env          # Windows
# cp .env.example .env          # Linux / macOS

# 4) Edit .env — lihat Bagian 4

# 5) Key + database
php artisan key:generate

# Buat DB kosong di MySQL, lalu:
php artisan migrate --force
php artisan db:seed --force

# 6) Storage upload
php artisan storage:link
mkdir storage\app\public\proposal-files
mkdir storage\app\public\laporan-akhir-files
mkdir storage\app\public\luaran-files
mkdir storage\app\public\peer-review-files
mkdir storage\app\public\form-kesediaan-files
mkdir storage\app\public\kkn-files

# 7) Jalankan
php artisan serve
```

Buka:
- Landing: `http://127.0.0.1:8000`
- Admin: `http://127.0.0.1:8000/admin`

Login admin default (setelah seed):
- **Email:** `admin@insos.test`
- **Password:** `password123`

---

## 4. Konfigurasi `.env`

Salin dari `.env.example`. Nilai penting:

```env
APP_NAME=SIINSOS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# WAJIB sama dengan APP_URL (satu host)
APP_DASHBOARD_URL="${APP_URL}"
APP_LANDING_URL="${APP_URL}"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=namadatabase
DB_USERNAME=root
DB_PASSWORD=
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://siinsos.domain-mitra.id
APP_DASHBOARD_URL="${APP_URL}"
APP_LANDING_URL="${APP_URL}"
```

Lalu:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 5. Database — dua jalur

### Jalur A — Migrate dari nol (disarankan untuk SIINSOS)

```sql
CREATE DATABASE dashboardta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate --force
php artisan db:seed --force
```

Ini membuat skema lengkap + akun uji (admin, mahasiswa, dosen).

### Jalur B — Import SQL backup + migrate tambahan

Gunakan jika mitra ingin data contoh dari pengembang:

1. Import file SQL (mis. `dashboardta final final.sql`) ke database kosong.
2. Jalankan `php artisan migrate --force` (hanya menambah kolom/tabel yang belum ada).
3. Seed opsional jika user admin belum ada.

**Jangan** `migrate:fresh` di production jika sudah ada data.

---

## 6. Storage & permission

```bash
php artisan storage:link
```

Pastikan web server bisa menulis:

- `storage/`
- `bootstrap/cache/`

Linux contoh:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Folder upload dokumen (dibuat otomatis saat upload, atau buat manual — lihat checklist Bagian 3).

---

## 7. Deploy web server

Document root mengarah ke:

```
.../siinsos/public
```

Contoh Nginx:

```nginx
root /path/ke/siinsos/public;
index index.php;
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

---

## 8. Verifikasi setelah setup

| Cek | Cara |
|-----|------|
| Landing terbuka | Buka `APP_URL` |
| Admin terbuka | Buka `APP_URL/admin` |
| Login admin | `admin@insos.test` / `password123` |
| Migrasi lengkap | `php artisan migrate:status` (semua *Ran*) |
| Storage link | URL `/storage/...` tidak 404 |
| Verifikasi pendaftar | Buka menu Verifikasi Pendaftar, buka detail kelompok |
| Penilaian akhir | Menu Penilaian Akhir menampilkan mahasiswa aktif |

SQL cepat:

```sql
SHOW COLUMNS FROM kkn_pendaftar LIKE 'status_verifikasi';
SELECT email, role, status FROM users WHERE role = 'admin';
```

---

## 9. Troubleshooting

| Gejala | Solusi |
|--------|--------|
| `Unknown column 'status_verifikasi'` | `php artisan migrate --force` |
| Halaman admin redirect salah / port 8001 | Set `APP_DASHBOARD_URL` = `APP_URL` |
| CSS rusak | Pastikan `public/css` dan `public/assets` ikut di-copy; clear cache browser |
| File upload 404 | `php artisan storage:link` + cek folder `storage/app/public/*-files` |
| `Connection refused` MySQL | Cek `DB_HOST` / service MySQL / nama DB |
| Login gagal setelah seed | Pastikan `role = 'admin'` dan `status = 'active'` di tabel `users` |
| Migrate gagal di tengah | Jangan `migrate:fresh` di data live; perbaiki error lalu `migrate` ulang |

---

## 10. Yang diserahkan ke mitra

Minimal:

1. Folder **`siinsos`** (kode sumber)
2. File **`PANDUAN_ADAPTASI_SIINSOS.md`** (dokumen ini)
3. Opsional: backup SQL `dashboardta` jika ingin data contoh
4. Kredensial admin default **harus diganti** setelah serah terima

Tidak wajib menyerahkan folder `project-akhir` atau `dashboard` terpisah.

---

## 11. Ringkasan arsitektur merge

```
siinsos/
├── routes/web.php          ← landing + mahasiswa/dosen
├── routes/admin.php        ← modul admin (/admin)
├── storage/app/public/     ← semua file upload
├── database/migrations/    ← skema lengkap satu DB
├── .env.example            ← template single-app
└── PANDUAN_ADAPTASI_SIINSOS.md
```

Koneksi DB `dashboard` / `project_akhir` dan disk `dashboard` tetap ada sebagai **alias ke dalam siinsos**, agar kode lama tetap berjalan tanpa dua aplikasi.
