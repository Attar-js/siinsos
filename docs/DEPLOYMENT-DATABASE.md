# Panduan Database — SIINSOS (Aplikasi Gabungan)

## Ringkasan

SIINSOS memakai **satu database MySQL**. Koneksi `dashboard` dan `project_akhir` di `config/database.php` adalah **alias** ke kredensial `DB_*` yang sama.

| Koneksi | Env | Peran |
|---------|-----|-------|
| `mysql` (default) | `DB_*` | Semua tabel aplikasi |
| `dashboard` | opsional `DASHBOARD_DB_*` (default = `DB_*`) | Alias legacy |
| `project_akhir` | opsional `PROJECT_AKHIR_DB_*` (default = `DB_*`) | Alias legacy |

Migrasi di `database/migrations` mencakup skema lengkap + migrasi sinkronisasi defensif (`hasTable` / `hasColumn`) agar aman di DB baru maupun DB yang sudah ada data.

---

## Langkah deploy database (server / device baru)

### 1. Buat database

```sql
CREATE DATABASE dashboardta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Atur `.env`

Salin dari `.env.example`, lalu set minimal:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dashboardta
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost:8000
APP_DASHBOARD_URL="${APP_URL}"
APP_LANDING_URL="${APP_URL}"
```

Tidak perlu membuat database kedua.

### 3. Migrasi + seed

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

Akun admin default setelah seed:
- Email: `admin@insos.test`
- Password: `password123`

### 4. Verifikasi

```sql
USE dashboardta;

SELECT 'users' AS tabel, COUNT(*) AS jumlah FROM users
UNION ALL SELECT 'groups', COUNT(*) FROM `groups`
UNION ALL SELECT 'kkn_pendaftar', COUNT(*) FROM kkn_pendaftar
UNION ALL SELECT 'proposal', COUNT(*) FROM proposal
UNION ALL SELECT 'penilaian', COUNT(*) FROM penilaian;
```

Pastikan kolom penting ada, misalnya:

```sql
SHOW COLUMNS FROM kkn_pendaftar LIKE 'status_verifikasi';
SHOW COLUMNS FROM users LIKE 'role';
```

---

## Catatan

- Jika import SQL backup lama, tetap jalankan `php artisan migrate --force` setelahnya agar kolom/tabel baru ikut terpasang.
- Detail setup lengkap: lihat `PANDUAN_ADAPTASI_SIINSOS.md` di root folder `siinsos`.
